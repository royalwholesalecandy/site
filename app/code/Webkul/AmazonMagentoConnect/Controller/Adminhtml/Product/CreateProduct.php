<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

class CreateProduct extends Product
{
    /**
     * @var \Webkul\AmazonMagentoConnect\Model\ProductMap
     */
    private $productMap;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Webkul\AmazonMagentoConnect\Logger\Logger
     */
    private $logger;

    private $productName;

    private $sku;

    private $productType;

    /**
     * @param Context                                       $context
     * @param \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap
     * @param \Magento\Framework\Json\Helper\Data           $jsonHelper
     * @param \Webkul\AmazonMagentoConnect\Helper\Data      $helper
     * @param \Webkul\AmazonMagentoConnect\Helper\Product   $productHelper
     */
    public function __construct(
        Context $context,
        \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        \Webkul\AmazonMagentoConnect\Helper\Product $productHelper,
        \Webkul\AmazonMagentoConnect\Helper\ManageProductRawData $manageProductRawData,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->productMap = $productMap;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->productHelper = $productHelper;
        $this->manageProductRawData = $manageProductRawData;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $accountId = $this->getRequest()->getParam('accountId');
        try {
            if ($accountId) {
                $backendSession = $this->_objectManager->get(
                    '\Magento\Backend\Model\Session'
                );
                $backendSession->setAmzSession('start');
                $this->helper->getAmzClient($accountId);
                $tempData = $this->helper
                            ->getTotalImported('product', $accountId);
                $request=$this->getRequest();
                $tempProData = json_decode($tempData->getItemData(), true);
                $result = [];
                $importType = $this->helper->getImportTypeOfProduct();
                $this->productName = $tempProData['name'];
                $this->sku = $tempProData['sku'];

                if (!empty($tempProData)) {
                    $this->productType = $tempProData['type_id'];
                    if ($this->productHelper->isProductMapped($tempData->getItemId())) {
                        $result = $this->processedTempData(
                            $importType,
                            $tempProData,
                            $tempData->getItemId(),
                            $request
                        );
                        $data = [
                            'amazon_pro_id' => $tempData->getItemId(),
                            'name' => $this->productName,
                            'product_type' => $this->productType,
                            'mage_amz_account_id'   => $accountId,
                            'amz_product_id'    => $tempData->getAmzProductId(),
                            'product_sku' => $this->sku,
                            'fulfillment_channel' => $tempProData['wk_fulfillment_channel'] === 'DEFAULT' ? 'FBM' : 'FBA'
                        ];
                    } else {
                        $result['error'] = 1;
                        $result['msg'] = 'Skipped '.$tempProData['name'].". sku '".
                                        $tempProData['sku']."' already mapped.";
                    }
                    
                    if (isset($result['product_id']) && $result['product_id']) {
                        $data['magento_pro_id'] = $result['product_id'];
                        $data['mage_cat_id'] = $tempProData['category'][0];
                        $record = $this->productMap;
                        $record->setData($data)->save();
                    }
                } else {
                    $result = [
                        'error' => true,
                        'msg' => 'Data not found'
                    ];
                }
                $tempData->delete();
                $backendSession->setAmzSession('');
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode($result)
                );
            } else {
                $data = $this->getRequest()->getParams();
                $total = (int) $data['count'] - (int) $data['skip'];
                $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ').$total.__(' Product(s) Imported.').'</div>';
                $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
                $result['msg'] = $msg;
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode($result)
                );
            }
        } catch (\Exception $e) {
            $this->logger->info('Product Controller : '.$e->getMessage());
            $result = [
                    'error' => true,
                    'msg' => 'Something went wrong.',
                    'actual_error' => $e->getMessage()
                ];
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($result)
            );
        }
    }

    /**
     * processed amazon temp data
     *
     * @param string $importType
     * @param array $tempProData
     * @param string $asin
     * @param object $request
     * @return array
     */
    private function processedTempData($importType, $tempProData, $asin, $request)
    {
        try {
            if (empty($importType)) {
                $cmptProData  = $this->helper->amzClient->getMatchingProduct([$asin]);
                $additionAmzAttrs = $this->manageProductRawData->getAdditionAttributes($cmptProData);
                $tempProData['addition_attrs'] = $additionAmzAttrs;
                $result = $this->_getSimpleProductResponse($tempProData, $request);
            } else {
                $hasVariation = $this->manageProductRawData
                                ->amzProHasVariation($asin, $tempProData);
                if (!empty($hasVariation['is_simple'])) {
                    $result = $this->_getSimpleProductResponse($hasVariation['data'], $request);
                } else {
                    foreach ($hasVariation['data'] as $key => $value) {
                        $request->setParam($key, $value);
                    }
                    $result = $this->productHelper
                            ->saveConfigProduct($request);
                    $this->productType = 'Configurable';
                    $this->productName = $hasVariation['data']['name'];
                    $this->sku = $hasVariation['data']['sku'];
                }
            }
        } catch (\Exception $e) {
            $result = [
                'error' => 1,
                'msg'   => 'Item not found.',
                'actual_error'  => $e->getMessage()
            ];
            $this->logger->info('Controller createProduct processedTempData : '.$e->getMessage());
        }
        
        return $result;
    }

    /**
     * get simple product data response
     *
     * @param array $tempProData
     * @param object $request
     * @return array
     */
    private function _getSimpleProductResponse($tempProData, $request)
    {
        $result = null;
        try {
            $isSkuExist = $this->productHelper->isValidSku($tempProData['sku']);
            if ($isSkuExist) {
                foreach ($tempProData as $key => $value) {
                    $request->setParam($key, $value);
                }
                $result = $this->productHelper
                        ->saveSimpleProduct($request);
            } else {
                $result = $this->productHelper
                        ->getExistingSkuData($tempProData['sku']);
            }
        } catch (\Exception $e) {
            $result = [
                'error' => 1,
                'msg'   => 'Item not found.',
                'actual_error'  => $e->getMessage()
            ];
            $this->logger->info('createProduct _getSimpleProductResponse : '.$e->getMessage());
        }
        return $result;
    }
}
