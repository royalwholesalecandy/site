<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttributeModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const URI = '/onca/xml';
    const CATALOGSEARCH_FULLTEXT = 'catalogsearch_fulltext';
    /*
    contain amazon client
    */
    private $amzClient;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var SaveProduct
     */
    private $saveProduct;

    /*
    AttributeFactory
     */
    private $attributeFactory;

    /*
    FormKey
     */
    private $formkey;

    /*
    \Magento\Framework\Filesystem
     */
    private $filesystem;

    /*
    \Magento\Framework\Registry
     */
    private $registry;

    /*
    \Webkul\AmazonMagentoConnect\Logger\Logger
     */
    private $logger;

    /*
    ConfigurableAttributeModel
     */
    private $configurableAttributeModel;

    /*
    ConfigurableProTypeModel
     */
    private $configurableProTypeModel;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param AttributeFactory $attributeFactory
     * @param FormKey $formkey
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param ConfigurableAttributeModel $configurableAttributeModel
     * @param ConfigurableProTypeModel $configurableProTypeModel
     * @param SaveProduct $saveProduct
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger
     * @param \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap
     * @param Data $helper
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product $product,
        AttributeFactory $attributeFactory,
        FormKey $formkey,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        ConfigurableAttributeModel $configurableAttributeModel,
        ConfigurableProTypeModel $configurableProTypeModel,
        SaveProduct $saveProduct,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        \Webkul\AmazonMagentoConnect\Model\ProductMap $productMap,
        Data $helper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory
    ) {
        $this->product = $product;
        $this->attributeFactory = $attributeFactory;
        $this->formkey = $formkey;
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->saveProduct = $saveProduct;
        $this->logger = $logger;
        $this->configurableAttributeModel = $configurableAttributeModel;
        $this->configurableProTypeModel = $configurableProTypeModel;
        $this->helper = $helper;
        $this->productMap = $productMap;
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        $this->indexerFactory = $indexerFactory;
        parent::__construct($context);
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
        return $this->amzClient;
    }
    /**
     * Save image in store.
     *
     * @param string $inPath
     * @param string $outPath
     */
    public function saveImage($inPath, $outPath)
    {
        try {
            $browserStr = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 '
                                    .'(KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $inPath);
            curl_setopt($ch, CURLOPT_USERAGENT, $browserStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            $file = fopen($outPath, 'w');
            if ($file === false) {
                $this->logger->info('saveImage : unable to open file');
                unlink($outPath);

                return false;
            }
            fwrite($file, $response);
            fclose($file);
            return true;
        } catch (\Exception $e) {
            $this->logger->info('saveImage : '.$e->getMessage());

            return false;
        }
    }

    /**
     * _getNewImagePath
     * @param string $imageUrl
     * @param string $productSku
     * @return string
     */

    private function _getNewImagePath($imageUrl, $productSku)
    {
        try {
            $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                                ->getAbsolutePath().'import/AmazonMagentoConnect/';
            $imgSrcExplode = explode('?', $imageUrl);
            $imgSrcExplode = explode('/', $imgSrcExplode[0]);
            $imageType = substr(strrchr($imgSrcExplode[count($imgSrcExplode) - 1], '.'), 1);
            $imgPath = $path.md5($imgSrcExplode[count($imgSrcExplode) - 1].$productSku)
                                .'.'.strtolower($imageType);
            return $imgPath;
        } catch (\Exception $e) {
            $this->logger->info('Product _getNewImagePath : '.$e->getMessage());
        }
    }

    /**
     * Add Images To Product.
     * @param int          $productId
     * @param string|array $images
     * @param int          $profileId
     */
    public function addImages($productId, $images)
    {
        try {
            $product = $this->product->load($productId);
            foreach ($images['images'] as $image) {
                $image = trim($image);
                if ($image != '') {
                    $imgPath = $this->_getNewImagePath($image, $product->getSku());
                    $this->saveImage($image, $imgPath);
                    if (file_exists($imgPath) && filesize($imgPath) > 0) {
                        if (function_exists('exif_imagetype')) {
                            $isPicture = exif_imagetype($imgPath) ? true : false;
                            if ($isPicture) {
                                $product->addImageToMediaGallery(
                                    $imgPath,
                                    ['image', 'small_image', 'thumbnail'],
                                    false,
                                    false
                                );
                                $product->save();
                            }
                        } else {
                            $product->addImageToMediaGallery(
                                $imgPath,
                                ['image', 'small_image', 'thumbnail'],
                                false,
                                false
                            );
                            $product->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Product addImages : '.$e->getMessage());
        }
    }

    /**
     * getAttributeInfo
     * @param string $mageAttrCode
     * @return false | Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private function _getAttributeInfo($mageAttrCode)
    {
        try {
            $attributeInfoColl = $this->attributeFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter(
                                            'attribute_code',
                                            ['eq' => $mageAttrCode]
                                        );
            $attributeInfo = false;
            foreach ($attributeInfoColl as $attrInfoData) {
                $attributeInfo = $attrInfoData;
            }
            return $attributeInfo;
        } catch (\Exception $e) {
            $this->logger->info('Product _getAttributeInfo : '.$e->getMessage());
        }
    }

    /**
     * Check for Valid Sku to Upload Product.
     * @param int|string $sku
     * @return bool
     */
    public function isValidSku($sku)
    {
        try {
            if ($sku == '') {
                return false;
            } else {
                return $this->product->getIdBySku($sku) ? false : true;
            }
        } catch (\Exception $e) {
            $this->logger->info('Product isValidSku : '.$e->getMessage());
        }
    }

    /**
     * check product already mapped or not
     *
     * @param string $asin
     * @return boolean
     */
    public function isProductMapped($asin)
    {
        $mappedColl = $this->productMap->getCollection()
                        ->addFieldToFilter('amazon_pro_id', ['eq' =>$asin]);
        if ($mappedColl->getSize()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * get Asin option value of an attribute
     * @return int
     */
    public function getOptionValByText($mageAttrCode, $mageAttrText)
    {
        $optionVal = null;
        try {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $mageAttrCode);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option) {
                if ($option['label'] === $mageAttrText) {
                    $optionVal = $option['value'];
                    break;
                }
            }
            return $optionVal;
        } catch (\Exception $e) {
            $this->logger->info('Product getDataFromReportByAsin : '.$e->getMessage());
        }
    }

    /**
     * Save Simple Product.
     * @param array $proData
     * @return array
     */
    public function saveSimpleProduct($proDataReq, $isAssociateProduct = 0, $attributeValues = [], $assocatedPro = [])
    {
        $this->getInitilizeAmazonClient();
        $proData = $proDataReq->getParams();
        $proDataReq->clearParams();
        $result = ['error' => 0];
        $hasWeight = 1;
        $categoryIds = [];
        if ($this->isValidSku($proData['sku'])) {
            $defaultWebsiteId = $this->helper->getDefaultWebsite();
            $wholeData = [
                'form_key' => $this->formkey->getFormKey(),
                'type' => $proData['type_id'],
                'new-variations-attribute-set-id' => $proData['attribute_set_id'],
                'set' => $proData['attribute_set_id']
            ];
            $wholeData['product']['name'] = $proData['name'];
            $wholeData['product']['sku'] = $proData['sku'];
            $wholeData['product']['price'] = $proData['price'];
            $wholeData['product']['website_ids'] = [$defaultWebsiteId];
            $wholeData['product']['tax_class_id'] = $proData['tax_class_id'];
            $wholeData['product']['quantity_and_stock_status']['qty'] = isset($proData['stock'])?$proData['stock']: 1;
            $wholeData['product']['quantity_and_stock_status']['is_in_stock'] = 1;
            $wholeData['product']['product_has_weight'] = $proData['weight'] ? true : false;
            $wholeData['product']['weight'] = $proData['weight'];
            $wholeData['product']['category_ids'] = $proData['category'];
            $wholeData['product']['description'] = str_replace('"', '', $proData['description']);
            $wholeData['product']['status'] = $proData['status'];
            $wholeData['product']['visibility'] = 4;
            $wholeData['product']['stock_data']['manage_stock'] = 1;
            $wholeData['product']['stock_data']['use_config_manage_stock'] = 1;
            $wholeData['product']['stock_data']['qty'] = isset($proData['stock']) ? $proData['stock'] : 1;
            if (isset($proData['wk_fulfillment_channel'])) {
                $fulfillmentChannel = $proData['wk_fulfillment_channel'] === 'DEFAULT' ? 'fbm' : 'fba' ;
                $wholeData['product']['wk_fulfillment_channel'] = $fulfillmentChannel;
            }
            if (isset($proData['asin']) && empty($assocatedPro)) {
                $wholeData['product']['identification_label'] = $this->getOptionValByText('identification_label', 'ASIN');
                $wholeData['product']['identification_value'] = $proData['asin'];

                $proData['image_data'] = $this->getAmzProImage($proData['asin']);
            }
            
            if (isset($proData['supperattr']) && count($proData['supperattr'])) {
                $wholeData['product']['supperattr'] = $proData['supperattr'];
                foreach ($proData['supperattr'] as $mageAttrCode) {
                    $attrInfo = $this->_getAttributeInfo($mageAttrCode);
                    if ($attrInfo) {
                        $wholeData['product']['attributes'][] = $attrInfo->getAttributeId();
                    }
                }
            }
            if (isset($proData['addition_attrs']) && count($proData['addition_attrs'])) {
                foreach ($proData['addition_attrs'] as $code => $codeVal) {
                    $wholeData['product'][$code]  = $codeVal;
                }
            }
            if ($isAssociateProduct == 1) {
                foreach ($attributeValues as $code => $value) {
                    $wholeData['product'][$code] = $value;
                }
                $wholeData['product']['visibility'] = 1;
                if (count($assocatedPro) > 0) {
                    $wholeData['type'] = 'simple';
                    $wholeData['product']['name'] = $assocatedPro['name'];
                    $wholeData['product']['description'] = $assocatedPro['description'];
                    $wholeData['product']['weight'] = $assocatedPro['weight'];
                    $wholeData['product']['sku'] = $assocatedPro['sku'];
                    $wholeData['product']['price'] = (float) $assocatedPro['price'];
                    $wholeData['product']['tax_class_id'] = trim($assocatedPro['tax_class_id']);
                    $wholeData['product']['quantity_and_stock_status']['qty'] = $assocatedPro['qty'];
                    $wholeData['product']['quantity_and_stock_status']['is_in_stock'] = 1;
                    $wholeData['product']['category_ids'] = $proData['category'];
                    $wholeData['product']['identification_label'] = $this->asinOptionVal;
                    $wholeData['product']['identification_value'] = $assocatedPro['asin'];
                    if (isset($proData['wk_fulfillment_channel'])) {
                        $fulfillmentChannel = $proData['wk_fulfillment_channel'] === 'DEFAULT' ? 'fbm' : 'fba' ;
                        $wholeData['product']['wk_fulfillment_channel'] = $fulfillmentChannel;
                    }
                    
                    if (isset($assocatedPro['addition_attrs'])
                    && count($assocatedPro['addition_attrs'])) {
                        foreach ($assocatedPro['addition_attrs'] as $code => $codeVal) {
                            $wholeData['product'][$code]  = $codeVal;
                        }
                    }
                    $proData['image_data'] = $this->getAmzProImage($assocatedPro['asin']);
                    $data = [
                        'amazon_pro_id' => $assocatedPro['asin'],
                        'name' => $assocatedPro['name'],
                        'product_type' => 'associate',
                        'mage_amz_account_id'   => $this->helper->accountId,
                        'product_sku' => $assocatedPro['sku'],
                        'fulfillment_channel' => isset($assocatedPro['wk_fulfillment_channel']) && $assocatedPro['wk_fulfillment_channel'] === 'DEFAULT' ? 'FBM' : (!isset($assocatedPro['wk_fulfillment_channel']) ? 'NONE' : 'FBA')
                    ];
                }
            }
            try {
                foreach ($wholeData as $key => $value) {
                    $proDataReq->setPostValue($key, $value);
                }

                $productId = (int) $this->saveProduct->saveProductData($proDataReq);
                isset($proData['image_data']) && !empty($proData['image_data'])?$this->addImages($productId, $proData['image_data']):'';

                if ($proData['type_id'] === 'configurable' && $productId && $isAssociateProduct) {
                    $data['magento_pro_id'] = $productId;
                    $record = $this->productMap;
                    $savedRecord = $record->setData($data)->save();
                }
            } catch (\Exception $e) {
                $productId = 0;
            }

            $result = $productId ? ['error' => 0, 'product_id' => $productId]:
                                    [
                                        'error' => 1,
                                        'msg' => 'Skipped '.$proData['name'].'. error in importing product.'
                                    ];
        } else {
            $productName = null;
            $productSku = null;
            if (count($assocatedPro) > 0) {
                $productName = $assocatedPro['name'];
                $productSku = $assocatedPro['sku'];
            } else {
                $productName = $proData['name'];
                $productSku = $proData['sku'];
            }
            $product = $this->productRepository->get($productSku);
            $productId = $product->getId();
            if (!empty($productId)) {
                $result = ['error' => 0, 'product_id' => $productId, 'msg' => 'already exist', 'sku' => $productSku];
            } else {
                $result['error'] = 1;
                $result['msg'] = 'Skipped '.$productName.". sku '".$productSku."' already exist.";
                $result['sku'] = $productSku;
            }
        }
        return $result;
    }

    /** get amazon product data **/
    public function getAmzProImage($amzProAsin)
    {
        $imageData = [];
        try {
            $cmptProData  = $this->amzClient->getMatchingProduct([$amzProAsin]);
            if (isset($cmptProData['GetMatchingProductResult']['Product'])) {
                $defaultImage = $cmptProData['GetMatchingProductResult']['Product']['AttributeSets']['ItemAttributes']['SmallImage']['URL'];
                
                $imageArr[] = $defaultImage = str_replace('._SL75_', '.UL1500', $defaultImage);
                if ($this->helper->getProductApiStatus()) {
                    $imageSet = $this->getImagesUsingProApi('ASIN', $amzProAsin);
                    $imageArr = !empty($imageSet) ? $imageSet : $imageArr;
                }

                $path = BP.'/pub/media/import/AmazonMagentoConnect/';
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                $imageData = [
                    'default' => $defaultImage,
                    'images' => $imageArr
                ];
            }
            return $imageData;
        } catch (\Exception $e) {
            $this->logger->info('Product getDataFromReportByAsin : '.$e->getMessage());
        }
    }

    /**
     * Save Configurable Product.
     * @param array $proData
     * @return array
     */
    public function saveConfigProduct($proDataReq)
    {

        try {
            $proData = $proDataReq->getParams();
            $proDataReq->clearParams();
            $finalResult = ['error' => 0];
            $attributes = [];
            $associatedProductIds = [];
            $flag = true;
            $error = 0;
            $attributeCodetemp = '';

            if (count($proData['supperattr'])) {
                foreach ($proData['supperattr'] as $attributeCode) {
                    $attributeCode = trim($attributeCode);
                    $attributeId = $this->isValidAttribute($attributeCode);
                    if ($attributeId) {
                        $attributes[] = $attributeId;
                    } else {
                        $flag = false;
                        $attributeCodetemp = $attributeCodetemp.$attributeCode.',';
                        break;
                    }
                }
            } else {
                $flag = false;
            }

            if ($flag) {
                $errors = [];
                foreach ($proData as $key => $value) {
                    $proDataReq->setPostValue($key, $value);
                }
                $configResult = $this->addAssociatedProduct($proDataReq);
                $errorCount = 0;
                foreach ($configResult as $res) {
                    if (isset($res['error']) && $res['error'] == 1) {
                        ++$error;
                        $errors[] = $res['msg'];
                    } else {
                        $associatedProductIds[] = $res;
                    }
                }
                if (count($associatedProductIds) > 0) {
                    $proData['is_in_stock'] = 1;
                    foreach ($proData as $key => $value) {
                        $proDataReq->setPostValue($key, $value);
                    }
                    $result = $this->saveSimpleProduct($proDataReq);

                    if ($result['error'] == 0) {
                        $productId = $result['product_id'];
                        $this->completeConfigProduct($productId, $associatedProductIds, $attributes);
                        $finalResult['product_id'] = $productId;
                        $finalResult['amz_sku'] = $proData['sku'];
                    } else {
                        $finalResult['error'] = 1;
                        $finalResult['msg'] = $result['msg'];
                    }
                } else {
                    $finalResult['error'] = 1;
                    $msg = 'Unable to create associated products.';
                    $finalResult['msg'] = implode('<br>', $errors);
                    $finalResult['msg'] = $msg.$finalResult['msg'];
                }
                if ($error > 0) {
                    if (count($associatedProductIds) == 0) {
                        $msg = 'Unable to create associated products.<br>';
                        $finalResult['msg'] = implode('<br>', $errors);
                        $finalResult['msg'] = $msg.$finalResult['msg'];
                    } else {
                        $finalResult['msg'] = implode('<br>', $errors);
                    }
                }
            } else {
                $finalResult['msg'] = 'Some of super attribute is not valid for product '.$proData['name'];
                $finalResult['error'] = true;
            }
            return $finalResult;
        } catch (\Exception $e) {
            $this->logger->info('Product saveConfigProduct : '.$e->getMessage());
        }
    }

    /**
     * Add Associated Product to Configurabel Product After Creating Products.
     * @param int   $productId
     * @param array $associatedProductIds
     * @param array $attributes
     */
    public function completeConfigProduct($productId, $associatedProductIds, $attributes)
    {
        $attributes = array_unique($attributes);
        $product = $this->product->load($productId);
        $count = 0;
        foreach ($attributes as $attributeId) {
            $data = [
                'attribute_id' => $attributeId,
                'product_id' => $productId,
                'position' => $count
            ];
            ++$count;
            $this->configurableAttributeModel->setData($data)->save();
        }
        $attributeSetId = $this->helper->getAttributeSet();
        $product->setTypeId('configurable');
        $product->setAffectConfigurableProductAttributes($attributeSetId);
        $this->configurableProTypeModel->setUsedProductAttributeIds($attributes, $product);
        $product->setNewVariationsAttributeSetId($attributeSetId);
        $product->setAssociatedProductIds($associatedProductIds);
        $product->setCanSaveConfigurableAttributes(true);
        try {
            $product->setStockData(['qty' => '0', 'is_in_stock' => 1]);
            $product->setQuantityAndStockStatus(['qty' => '0', 'is_in_stock' => 1]);
            $product->save();
            // reindex catalog search index
            $indexer = $this->indexerFactory->create()->load(self::CATALOGSEARCH_FULLTEXT);
            $indexer->reindexAll();
        } catch (\Exception $e) {
            $this->logger->info('completeConfigProduct : '.$e->getMessage());
            return [
                'error' => 1,
                'msg' => __('Skip the product'),
                'actual_error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Associated Product of Configurable Product.
     * @param int $customerId
     * @param int $profileId
     * @param int $row
     * @return array
     */
    public function addAssociatedProduct($proDataReq)
    {
        try {
            $proData = $proDataReq->getParams();
            $result = [];
            $parentData = [];
            $attributeValues = [];
            foreach ($proData['assocate_pro'] as $assocatedPro) {
                $flag = true;
                foreach ($proData['supperattr'] as $supAttrCode) {
                    if (!isset($assocatedPro[$supAttrCode])) {
                        $flag = false;
                        break;
                    } else {
                        $attributeValues[$supAttrCode] = $assocatedPro[$supAttrCode];
                    }
                }
                if ($flag) {
                    foreach ($proData as $key => $value) {
                        $proDataReq->setPostValue($key, $value);
                    }
                    $proInfo = $this->saveSimpleProduct($proDataReq, 1, $attributeValues, $assocatedPro);

                    if (isset($proInfo['product_id'])) {
                        $result[] = $proInfo['product_id'];
                    } else {
                        // $this->logger->info('addAssociatedProduct : '.$proInfo['msg']);
                        $result['msg'] = $proInfo['msg'];
                        $result['error'] = 1;
                        continue;
                    }
                    $this->registry->unregister('product');
                    $this->registry->unregister('current_product');
                    $this->registry->unregister('current_store');
                } else {
                    $result['msg'] = __('Some of super attribute is Not Valid for product ').$proData['name'];
                    $result['error'] = 1;

                    return $result;
                }
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->info('Product addAssociatedProduct : '.$e->getMessage());
        }
    }

    /**
     * Check Attribute Code is Valid or Not for Configurable Product.
     *
     * @param string $attributeCode
     *
     * @return bool
     */
    public function isValidAttribute($attributeCode)
    {
        try {
            $attribute = $this->attributeFactory->create()->getCollection()
                                            ->addFieldToFilter('attribute_code', ['eq' => $attributeCode])
                                            ->addFieldToFilter('frontend_input', 'select')
                                            ->getFirstItem();

            return $attribute->getId() ? $attribute->getId() : false;
        } catch (\Exception $e) {
            $this->logger->info('Product isValidAttribute : '.$e->getMessage());
        }
    }

    /**
     * get existing product data
     *
     * @param string $sku
     * @return array
     */
    public function getExistingSkuData($sku)
    {
        $result = [];
        $productId = $this->product->getIdBySku($sku);
        if (!empty($productId)) {
            $result = ['error' => 0, 'product_id' => $productId, 'info' => 'already exist'];
        }
        return $result;
    }


    /**
     * make a call for sign url
     *
     * @param string $url
     * @return string
     */
    public function getPage($url)
    {
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($curl);
        curl_close($curl);
        return $html;
    }

    /**
     * make sign string url to call product adversting api
     *
     * @param string $IdType
     * @param string $ItemId
     * @return array | null
     */
    public function getImagesUsingProApi($IdType, $ItemId, $recursiveCal = 1)
    {
        $config = $this->helper->config;
        $params = [
            "Service" => "AWSECommerceService",
            "Operation" => "ItemLookup",
            "AWSAccessKeyId" => $config['pro_api_access_key_id'],
            "AssociateTag" => $config['associate_tag'],
            "ItemId" => $ItemId,
            "IdType" => $IdType,
            "ResponseGroup" => "Images,ItemAttributes,Variations,VariationImages"
        ];

        if (!isset($params["Timestamp"])) {
            $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
        }
        ksort($params);

        $pairs = [];

        foreach ($params as $key => $value) {
            array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
        }

        $canonicalQueryString = join("&", $pairs);
        $endPoint = $this->helper->getProductApiEndPoint();
        $stringToSign = "GET\n".$endPoint."\n".self::URI."\n".$canonicalQueryString;
        $signature = base64_encode(hash_hmac("sha256", $stringToSign, $config['pro_api_secret_key'], true));
        $requestUrl = 'http://'.$endPoint.self::URI.'?'.$canonicalQueryString.'&Signature='.rawurlencode($signature);
        $response = $this->getPage($requestUrl);
        $pxml = @simplexml_load_string($response);

        if ($pxml === false) {
            return false;
        } else {
            $jsonString = json_encode($pxml);
            $responseArray = json_decode($jsonString, true);
            $imageSet = $this->getImageSet($responseArray);
            return $imageSet;
        }
    }

    /**
     * get all images of product
     *
     * @param array $response
     * @return array
     */
    public function getImageSet($response)
    {
        $images = [];
        try {
            if (isset($response['Items']['Item']['ImageSets']['ImageSet'])) {
                $imageSet = $response['Items']['Item']['ImageSets']['ImageSet'];
                $imageSet = isset($imageSet[0]) ? $imageSet : [0 => $imageSet];
                foreach ($imageSet as $image) {
                    $images[] = str_replace('._SL75_', '.UL1500', $image['SmallImage']['URL']);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Product getImageSet : '.$e->getMessage());
        }
        return $images;
    }
}
