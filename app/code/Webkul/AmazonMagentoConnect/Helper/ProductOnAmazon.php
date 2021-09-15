<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Helper;

use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;

class ProductOnAmazon extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SELECTED_PRICE_RULE = 'export';

    private $amzClient;

    /*
    \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /*
    Data
     */
    private $helper;

    /*
    \Webkul\AmazonMagentoConnect\Model\ProductMap
     */
    private $productMap;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Data $helper
     * @param \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
        \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->productMap = $productMap;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->objectManager = $objectManager;
    }

    /**
     * get amazon client
     *
     * @return object
     */
    public function getInitilizeAmazonClient()
    {
        if (!$this->amzClient) {
            $this->amzClient = $this->helper->getAmzClient();
        }
    }

    /**
     * mange magento product to sync to amazon
     * @param  array $params
     * @return array
     */
    public function manageMageProduct($productIds, $isFba = 0, $isUpdate = 0)
    {
        $this->getInitilizeAmazonClient();
        $result = null;
        $postProductData = [];
        $postNewData = [];
        $exportErrorsData = [];
        $totalCount = count($productIds);
        $errorCount = 0;
        $amzCurrencyCode = $this->helper->config['currency_code'];
        $currencyRate = $this->helper->getCurrencyRate($amzCurrencyCode);
        foreach ($productIds as $productId) {
            $product = $this->productFactory->create()
                        ->load($productId);
            if ($product->getEntityId()) {
                $exportProType = $this->helper
                    ->getProductAttrValue($product, 'identification_label');
                $exportProValue = $this->helper
                    ->getProductAttrValue($product, 'identification_value');
                $mwsProduct = $this->objectManager->create('Webkul\AmazonMagentoConnect\Helper\MwsProduct');
                $mwsProduct->mageProductId = $product->getId();
                $mwsProduct->sku = $product->getSku();
                $actualPrice = empty($currencyRate) ? $product->getPrice() : ($product->getPrice()*$currencyRate);
                $ruleAppliedPrice = '';
                if ($ruleData = $this->helper->getPriceRuleByPrice($actualPrice)) {
                    $ruleAppliedPrice = $this->helper->getPriceAfterAppliedRule($ruleData, $actualPrice, self::SELECTED_PRICE_RULE);
                }
                $newPrice = empty($ruleAppliedPrice) ? $actualPrice : $ruleAppliedPrice;
                $newPrice = str_replace(',', '', number_format($newPrice, 2));
                $mwsProduct->price = $newPrice;
                $mwsProduct->productId = $exportProValue;
                $mwsProduct->productIdType = $exportProType;
                $mwsProduct->conditionType = 'New';
                $mwsProduct->quantity = $product->getQuantityAndStockStatus()['qty'];
                if ($mwsProduct->validate()) {
                    $postProductData[] = $mwsProduct;
                    $attrs = $this->helper->getAmzAttrWithVal($product);
                    $postNewData[] = $attrs;
                    $stockProductData[] = $this->updateQtyData($product, $isFba, $isUpdate);
                    $PriceProductData[] = $this->updatePriceData($product);
                    $imageProductData[] = $this->postImages($product);
                } else {
                    $exportErrorsData[] = $mwsProduct->getValidationErrors();
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }
        $exportedProducts = $totalCount - $errorCount;
        if (!empty($postProductData)) {
            $imgResponse = '';
            $result        = $this->amzClient->postProduct($postNewData);
            $stockResponse = $this->amzClient->updateStock($stockProductData);
            $priceResponse = $this->amzClient->updatePrice($PriceProductData);
            if ($this->helper->isImgExported()) {
                $imgResponse   = $this->amzClient->postImages($imageProductData);
            }
            $this->saveDataInTable(
                $postProductData,
                $result,
                $stockResponse,
                $priceResponse,
                $imgResponse,
                $isFba,
                $isUpdate
            );
        }

        empty($errorCount) ? '' : $this->logger->info('Helper ProductOnAmazon manageMageProduct : error log '.json_encode($exportErrorsData));
        
        return ['count' => $exportedProducts, 'error_count'=>$errorCount];
    }

    /**
     * save exported data in table
     *
     * @param object $submitedData
     * @param array $mwsResponse
     * @return void
     */
    public function saveDataInTable(
        $submitedData,
        $mwsResponse,
        $stockRespone,
        $priceResponse,
        $imgResponse = false,
        $isFba = 0,
        $isUpdate = 0
    ) {
        if (isset($mwsResponse['FeedSubmissionId']) && $mwsResponse['FeedSubmissionId']) {
            foreach ($submitedData as $subProduct) {
                $product = $this->productFactory->create()
                            ->load($subProduct->mageProductId);
                $fulfillCode = $isFba ? 'FBA' : 'FBM';
                
                if ($isUpdate) {
                    $proMapCol = $this->productMap->getCollection()
                                ->addFieldToFilter('magento_pro_id', $subProduct->mageProductId);
                    foreach ($proMapCol as $record) {
                        $record->setFulfillmentChannel($fulfillCode);
                        $record->setFeedsubmissionId($mwsResponse['FeedSubmissionId']);
                        $record->setQtyFeedsubmissionId($stockRespone['FeedSubmissionId']);
                        $record->setPriceFeedsubmissionId($priceResponse['FeedSubmissionId']);
                        $record->save();
                    }
                } else {
                    $cats = $product->getCategoryIds();
                    $firstCategoryId = null;
                    if (count($cats)) {
                        $firstCategoryId = $cats[0];
                    }
                    $data = [
                        'magento_pro_id'        => $product->getEntityId(),
                        'mage_cat_id'           => $firstCategoryId,
                        'name'                  => $product->getName(),
                        'product_type'          => $product->getTypeId(),
                        'amazon_pro_id'         => '',
                        'mage_amz_account_id'   => $this->helper->accountId,
                        'amz_product_id'        => $subProduct->productId,
                        'feedsubmission_id'     => $mwsResponse['FeedSubmissionId'],
                        'qty_feedsubmission_id' => $stockRespone['FeedSubmissionId'],
                        'price_feedsubmission_id'   => $priceResponse['FeedSubmissionId'],
                        'img_feedsubmission_id' => is_array($imgResponse)?$imgResponse['FeedSubmissionId'] : '',
                        'export_status'         =>'0',
                        'error_status'          =>'0',
                        'pro_status_at_amz'     =>'3',
                        'product_sku'           => $subProduct->sku,
                        'fulfillment_channel'   => $fulfillCode
                    ];
                    $record = $this->productMap;
                    $record->setData($data)->save();
                }
                $product->setWkFulfillmentChannel(strtolower($fulfillCode))->save();
            }
        }
    }

    /**
     * get product quantity related data
     * @param  object $product
     * @return array
     */
    public function updateQtyData($product, $isFba = 0, $isUpdate = 0)
    {
        if ($isFba) {
            $updateQuantityArray = [
                'SKU'                   => $product->getSku(),
                'FulfillmentCenterID'   => 'AMAZON_'.$this->helper->getAmazonCountry(),
                'Lookup'                => 'FulfillmentNetwork',
                'SwitchFulfillmentTo'   => 'AFN',
            ];
        } else {
            $updateQuantityArray = [
                'SKU'   => $product->getSku(),
                'Quantity'   => $product->getQuantityAndStockStatus()['qty'],
            ];
            if ($isUpdate && ($product->getWkFulfillmentChannel() !== 'fbm')) {
                $updateQuantityArray['SwitchFulfillmentTo'] = 'MFN';
            }
        }
        return $updateQuantityArray;
    }

    /**
     * get image data for submit feed
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function postImages($product)
    {

        $imageArray = [
            'sku'   => $product->getSku(),
            'image_location' => $this->helper->getPictureUrl($product),
            'image_type' => 'Main',
        ];
        return $imageArray;
    }

    /**
     * get product price related data
     * @param  object $product
     * @return array
     */
    public function updatePriceData($product)
    {
        $amzCurrencyCode = $this->helper->config['currency_code'];
        $currencyRate = $this->helper->getCurrencyRate($amzCurrencyCode);
        $price = empty($currencyRate) ? round($product->getPrice(), 2) : round(($product->getPrice()*$currencyRate), 2);
        $updatePriceArray = [
            'sku'   => $product->getSku(),
            'price' => $price
        ];
        return $updatePriceArray;
    }

    /**
     * check exported product status
     *
     * @param array $feedIds
     * @return void
     */
    public function checkProductFeedStatus($feedIds)
    {
        $this->getInitilizeAmazonClient();
        $productFeedStatus = [];
        $result = $this->feedSubmitionResult($feedIds);
    }

    /**
     * proccessed exported product response
     *
     * @param [type] $feed
     * @param [type] $feedResponse
     * @return void
     */
    public function processFeedResult($feed, $feedResponse)
    {
        try {
            $response = [];
            $updatedRecods = 0;
            $failedErrorCodes = ['8058','8560','8047','8105','6024'];
            foreach ($feedResponse as $feedArray) {
                $errorCode = '';
                $errorMsg = '';
                $productAsign = null;
                $productStatus = null;
                $mapProductData = $this->productMapRepo->getBySku($feedArray['product_sku']);
                if ($mapProductData->getSize()) {
                    if (in_array($feedArray['error_code'], $failedErrorCodes)) {
                        $productStatus = '0';//failed
                        $errorMsg = $feedArray['error_msg']. '(error code '.$feedArray['error_code']. ')';
                    } else {
                        $amzProData = $this->amzClient->getMyPriceForSKU([$feedArray['product_sku']]);
                        if (isset($amzProData['GetMyPriceForSKUResult']['Product'])) {
                            $productStatus = '1';//active
                            $amzProductForSku = $amzProData['GetMyPriceForSKUResult']['Product'];
                            $productAsign = $amzProductForSku['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
                        } else {
                            $productStatus = '2';//inactive
                        }
                    }
                    foreach ($mapProductData as $proData) {
                        $proData->setExportStatus('1');
                        $proData->setErrorStatus($errorMsg);
                        $proData->setProStatusAtAmz($productStatus);
                        $proData->setAmazonProId($productAsign);
                        $proData->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon processFeedResult : '.$e->getMessage());
        }
    }

    /**
     * send feed submit request
     */
    public function feedSubmitionResult($feedIds)
    {
        try {
            $feedResponse = [];
            foreach ($feedIds as $key => $feed) {
                $feedResponse = $this->convertTxtToArray($this->amzClient->getFeedSubmissionResult($feed));
                $this->processFeedResult($feed, $feedResponse);
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon feedSubmitionResult : '.$e->getMessage());
        }
        return $feedResponse;
    }

    /**
     * convert text to array
     *
     * @param string $content
     * @return array
     */
    public function convertTxtToArray($content)
    {
        try {
            $reportContent = str_replace([ "\n" , "\t" ], [ "[NEW*LINE]" , "[tAbul*Ator]" ], $content);
            $reportArr = explode("[NEW*LINE]", $reportContent);
            $i = 4;
            $exportErrors = [];
            // $reportHeadingArr = explode("[tAbul*Ator]", utf8_encode($reportArr[4]));
            for ($i =5; $i < count($reportArr); $i++) {
                $errorReport = explode("[tAbul*Ator]", utf8_encode($reportArr[$i]));
                if (isset($errorReport[1])) {
                    $exportErrors[] = [
                        'product_sku' => $errorReport[1],
                        'error_code' => $errorReport[2],
                        'error_msg' => $errorReport[4]
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon convertTxtToArray : '.$e->getMessage());
        }
        return $exportErrors;
    }

    /**
     * check product status by sku
     *
     * @param array $amazProSku
     * @return void
     */
    public function checkProductStatusBySku($amazProSku)
    {
        try {
            $this->getInitilizeAmazonClient();
            $exportedAmzIds = [];
            $response = $this->amzClient->getCompetitivePricingForSKU($amazProSku);
            if (isset($response['GetCompetitivePricingForSKUResult'])) {
                foreach ($response['GetCompetitivePricingForSKUResult'] as $result) {
                    if (isset($result['Product'])) {
                        $asinData = $result['Product']['Identifiers']['MarketplaceASIN'];
                        $skuData = $result['Product']['Identifiers']['SKUIdentifier'];
                        $exportedAmzIds[$skuData['SellerSKU']] = $asinData['ASIN'];
                    }
                }
            }
            foreach ($exportedAmzIds as $sku => $asin) {
                $mapProductData = $this->productMapRepo->getBySku($sku);
                foreach ($mapProductData as $proData) {
                    $proData->setExportStatus('1');
                    $proData->setProStatusAtAmz('1');
                    $proData->setAmazonProId($asin);
                    $proData->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon checkProductStatusBySku : '.$e->getMessage());
        }
    }
}
