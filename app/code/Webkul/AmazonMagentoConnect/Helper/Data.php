<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Helper;

use Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Api\AccountsRepositoryInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    private static $_exportStatus = [
            0 => 'Pending',
            1 => 'Complete',
    ];

    private static $_amzProStatus = [
            0 => 'Failed',
            1 => 'Active',
            2 => 'Inactive',
            3 => 'Pending'
    ];

    private static $_operation = [
        '' => '--Select--',
        'Increase' => 'Increase',
        'Decrease' => 'Decrease',
    ];

    private static $_operationType = [
            '' => '--Select--',
            'Fixed' => 'Fixed',
            'Percent' => 'Percent',
    ];

    private static $_status = [
        '1' => 'Enable',
        '0' => 'Disable',
    ];

    /*
    contain seller account id
    */
    public $accountId;

    /*
    contain amazon client
    */
    public $amzClient;

    /*
    contain configuration of seller
    */
    public $config;
    /*
    contain seller id
     */
    public $sellerId;

    /*
    contain the secret key
     */
    public $secretKey;

    /*
    contain country id
     */
    public $region;

    /*
    contain error information
     */
    public $error;

    /*
    contain amazon access key id
     */
    public $accessKeyId;
    /**
     * @var string
     */
    private $attributeSetId;

    /**
     * @var AmazonTempDataRepositoryInterface
     */
    private $amazonTempDataRepo;

    /**
     * \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param AmazonTempDataRepositoryInterface $amazonTempDataRepo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $currencyInterface
     * @param ProductMapRepositoryInterface $productMapRepo
     * @param AccountsRepositoryInterface $accountsRepository
     * @param \Webkul\AmazonMagentoConnect\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        AmazonTempDataRepositoryInterface $amazonTempDataRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        ProductMapRepositoryInterface $productMapRepo,
        AccountsRepositoryInterface $accountsRepository,
        \Webkul\AmazonMagentoConnect\Logger\Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\AmazonMagentoConnect\Model\Config\Source\AmazonMarketplace $amzMarketplace,
        \Webkul\AmazonMagentoConnect\Model\Storage\DbStorage $dbStorage,
        \Webkul\AmazonMagentoConnect\Model\AttributeMapFactory $attributeMapFactory,
        \Webkul\AmazonMagentoConnect\Model\Config\Source\AmazonProAttribute $amazonProAttribute,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        parent::__construct($context);
        $this->amazonTempDataRepo = $amazonTempDataRepo;
        $this->storeManager = $storeManager;
        $this->currencyInterface = $currencyInterface;
        $this->productMapRepo = $productMapRepo;
        $this->accountsRepository = $accountsRepository;
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->amzMarketplace = $amzMarketplace;
        $this->dbStorage = $dbStorage;
        $this->attributeMapFactory = $attributeMapFactory;
        $this->amazonProAttribute = $amazonProAttribute;
        $this->moduleManager = $moduleManager;
    }

    /**
     * \Magento\Framework\Module\Manager
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    /**
     * save records in table
     *
     * @param string $tableName
     * @param array $data
     * @return int
     */
    public function saveInDbTable($tableName, $data)
    {
        if (!empty($data)) {
            $this->dbStorage->insertMultiple($tableName, $data);
        }
    }

    /**
     * truncate a table
     *
     * @param string $tableName
     * @return void
     */
    public function truncateTable($tableName)
    {
        $this->dbStorage->truncateTable($tableName);
    }

    /**
     * get mapped attribute data
     *
     * @return array
     */
    public function getMappedAttributeData()
    {
        $amazonAttrCodes = [];
        $attributeColl = $this->attributeMapFactory->create()->getCollection();
        foreach ($attributeColl as $value) {
            $amazonAttrCodes[$value->getAmzAttr()] = $value->getMageAttr();
        }
        return $amazonAttrCodes;
    }

    /**
     * get amazon proudct with value
     *
     * @param \Magento\Catalog\Product\Model $product
     * @return array
     */
    public function getAmzAttrWithVal($product)
    {
        $mappedAttrs = $this->getMappedAttributeData();
        $amzAttrs = $this->amazonProAttribute->toArray();
        $amazAttrsValues = [];
        foreach ($amzAttrs as $key => $value) {
            if (isset($mappedAttrs[$key]) && $key !== 'qty') {
                $attrValue = $product->getResource()->getAttribute($mappedAttrs[$key]);
                if ($attrValue) {
                    $attrValue = $attrValue->getFrontend()->getValue($product);
                    $attrValue = is_array($attrValue) ? implode(", ", $attrValue) : $attrValue;
                    $attrValue = $attrValue == 'No' ? '' : $attrValue;
                    $amazAttrsValues[$key] = $attrValue;
                }
            } else {
                $amazAttrsValues[$key] = '';
            }
        }
        $amazAttrsValues['qty'] = $product->getQuantityAndStockStatus()['qty'];
        return $amazAttrsValues;
    }

    /**
     * get array of perform operation
     *
     * @return array
     */
    public function getOperations()
    {
        return self::$_operation;
    }

    /**
     * get opeation type
     *
     * @return array
     */
    public function getOperationsTypes()
    {
        return self::$_operationType;
    }

    /**
     * get rule status
     *
     * @return array
     */
    public function getStatus()
    {
        return self::$_status;
    }

    /**
     * get all stores of amazon
     *
     * @return array
     */
    public function getAllAmazonStores()
    {
        $amzStores = [];
        $accountCol = $this->objectManager
                    ->create('Webkul\AmazonMagentoConnect\Model\AccountsFactory')
                    ->create()->getCollection();
        
        foreach ($accountCol as $account) {
            $amzStores[$account->getId()] = $account->getStoreName();
        }
        return $amzStores;
    }

    /**
     * check status of product adversitesing api
     *
     * @return void
     */
    public function getProductApiStatus()
    {
        return $this->config['all_images'];
    }

    /**
     * check status of product adversitesing api
     *
     * @return void
     */
    public function isDelFromCatalog()
    {
        return $this->config['del_from_catalog'];
    }

    /**
     * check image exported or not
     *
     * @return void
     */
    public function isImgExported()
    {
        return $this->config['export_image'];
    }

    /**
     * get amazon feed status
     * @return string
     */
    public function getExportStatus($status)
    {
        return self::$_exportStatus[$status];
    }

    /**
     * get amazon product status
     * @return int
     */
    public function getAmzProductStatus($status)
    {
        if ($status!== '') {
            $status = self::$_amzProStatus[$status];
        } else {
            $status = '';
        }
        return $status;
    }

    /**
     * get list of required states
     *
     * @return array
     */
    public function getRequiredStateList()
    {
        return $this->scopeConfig->getValue(
            'general/region/state_required',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * get import product type
     * @return int
     */
    public function getImportTypeOfProduct()
    {
        return $this->config['product_create'];
    }

    /**
     * get config value of priceurl
     * @return string
     */
    public function getPriceRuleConfigValue()
    {
        return $this->config['price_rule'];
    }

    /**
     * get status of amazon magento product
     * @return int
     */
    public function getItemReviseStatus()
    {
        return $this->config['revise_item'];
    }

    /**
     * get default website for amazon product
     * @return int|null
     */
    public function getDefaultWebsite()
    {
        return $this->config['default_website'];
    }

    /**
     * get default product qty
     * @return int
     */
    public function getDefaultProductQty()
    {
        return $this->config['default_qty'];
    }

    /**
     * get default product weight
     * @return int
     */
    public function getDefaultProductWeight()
    {
        return $this->config['default_weight'];
    }

    /**
     * get currency code of amazon account
     * @return string
     */
    public function getAmazonCurrencyCode()
    {
        return $this->config['currency_code'];
    }

    /**
     * get attribute set id
     * @param  int $accountId
     * @return int
     */
    public function getAttributeSet($accountId = false)
    {
        return $this->config['attribute_set'];
    }

    /**
     * get default Category
     * @return int
     */
    public function getDefaultCategory()
    {
        return $this->config['default_cate'];
    }

    /**
     * get default store for order sync
     * @return int
     */
    public function getDefaultStoreOrderSync()
    {
        return $this->config['default_store_view'];
    }

    public function getAmazonCountry()
    {
        return $this->config['country'];
    }

    /**
     * get shipped order status
     *
     * @return string
     */
    public function getShippedOrderStatus()
    {
        return $this->config['shipped_order'];
    }

    /**
     * get unshipped order status
     *
     * @return string
     */
    public function getUnshippedOrderStatus()
    {
        return $this->config['unshipped_order'];
    }

    /**
     * get Partially shipped order status
     *
     * @return string
     */
    public function getPartiallyshippedOrderStatus()
    {
        return $this->config['partiallyshipped_order'];
    }

    /**
     * get order status
     * @return int
     */
    public function getOrderStatus($orderStatus)
    {
        return (
            $orderStatus === 'Shipped' ? $this->getShippedOrderStatus() : (
                $orderStatus == 'Unshipped' ? $this->getUnshippedOrderStatus() : $this->getPartiallyshippedOrderStatus()));
    }

    /**
     * check street address line
     * @return int
     */
    public function getStreetLineNumber()
    {
        return $this->scopeConfig
                ->getValue('customer/address/street_lines');
    }

    /**
     * get count of imported item
     * @param  string $itemType
     * @param  int $accountId
     * @return object
     */
    public function getTotalImported($itemType, $accountId, $all = false)
    {
        $collection = $this->amazonTempDataRepo
        ->getCollectionByAccountIdnItemType($itemType, $accountId);
        if ($all) {
            return $collection;
        } else {
            foreach ($collection as $record) {
                return $record;
            }
        }
    }
    
    /**
     * get exported pending status count
     *
     * @param int $accountId
     * @return void | object
     */
    public function getExportedProColl($accountId)
    {
        $productMapColl = $this->productMapRepo->getCollectionByAccountId($accountId);
        $productMapColl->addFieldToFilter('export_status', 0);
        return $productMapColl;
    }

    /**
     * [getAmazonURL - get URL by country iso]
     * @return [type] [description]
     */
    public function getProductApiEndPoint()
    {
        $region = $this->config['country'];
        if ($region == 'US') {
            return ('webservices.amazon.com');
        } elseif ($region == 'CA') {
            //return ('mws.amazonservices.ca');
            return ('webservices.amazon.ca');
        } elseif ($region == 'JP') {
            return ('webservices.amazon.co.jp');
        } elseif ($region == 'MX') {
            //return ('mws.amazonservices.com.mx');
            return ('webservices.amazon.com.mx');
        } elseif ($region == 'CN') {
            return ('webservices.amazon.cn');
        } elseif ($region == 'IN') {
            return ('webservices.amazon.in');
        } elseif ($region == 'DE') {
            return ('webservices.amazon.de');
        } elseif ($region == 'BR') {
            return ('webservices.amazon.com.br');
        } elseif ($region == 'FR') {
            return ('webservices.amazon.fr');
        } elseif ($region == 'IT') {
            return ('webservices.amazon.it');
        } else {
            $msg = 'Incorrect Region Code';
        }
    }

    /**
     * get product attribute's value
     * @param  object $product
     * @param  string $attributCode
     * @return string
     */
    public function getProductAttrValue($product, $attributCode)
    {
        return (string)$product->getResource()->getAttribute($attributCode)
                ->getFrontend()->getValue($product);
    }

    /**
     * get all allowed currency
     *
     * @return array
     */
    public function getAllowedCurrencies()
    {
        $currenciesArray = [];
        $availableCurrencies = $this->storeManager->getStore()->getAvailableCurrencyCodes();
        foreach ($availableCurrencies as $currencyCode) {
            $currenciesArray[] = $currencyCode;
        }
        return $currenciesArray;
    }

    /**
     * get currency rate
     *
     * @param string $currency
     * @return void | int
     */
    public function getCurrencyRate($currency)
    {
        return $this->storeManager->getStore()
                    ->getBaseCurrency()->getRate($currency);
    }

        /**
         * get amazon client
         *
         * @param boolean $sellerId
         * @param boolean $amzCredentails
         * @return void
         */
    public function validateAmzCredentials($amzCredentails = false)
    {
        try {
            $config = [
                'Seller_Id' => $amzCredentails['seller_id'],
                'Marketplace_Id' => $amzCredentails['marketplace_id'],
                'Access_Key_ID' => $amzCredentails['access_key_id'],
                'Secret_Access_Key' => $amzCredentails['secret_key'],
                'MWSAuthToken' => $amzCredentails['marketplace_id'],
                'Application_Version' => '0.0.*'
            ];
            $this->amzClient = new MwsClient($config);
            if ($this->amzClient->validateCredentials()) {
                return $this->amzClient;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->info('Data validateAmzCredentials : '.$e->getMessage());
            return false;
        }
    }

    /**
     * get configuration field of amazon
     *
     * @param string $field
     * @return void
     */
    public function getStoreConfig($field)
    {
        return $this->scopeConfig->getValue("mpamazonconnect/configuration/$field");
    }

    /**
     * get Amazon client
     *
     * @param boolean $sellerId
     * @return void
     */
    public function getAmzClient($accountId = false, $needFresh = false)
    {
        try {
            if ($needFresh || !$this->accountId) {
                $this->accountId = $accountId;
            }
            if (!$this->amzClient) {
                $amzCredentails = $this->getSellerAmzCredentials($this->accountId)->toArray();
                $this->config = [
                    'attribute_set'         => $amzCredentails['attribute_set'],
                    'Seller_Id'             => $amzCredentails['seller_id'],
                    'Marketplace_Id'        => $amzCredentails['marketplace_id'],
                    'Access_Key_ID'         => $amzCredentails['access_key_id'],
                    'Secret_Access_Key'     => $amzCredentails['secret_key'],
                    'MWSAuthToken'          => $amzCredentails['marketplace_id'],
                    'Application_Version'   => '0.0.*',
                    'inventory_report_id'   => $amzCredentails['inventory_report_id'],
                    'listing_report_id'     => $amzCredentails['listing_report_id'],
                    'default_cate'          => $amzCredentails['default_cate'],
                    'default_store_view'    => $amzCredentails['default_store_view'],
                    'product_create'        => $amzCredentails['product_create'],
                    'default_website'       => $amzCredentails['default_website'],
                    'order_status'          => $amzCredentails['order_status'],
                    'associate_tag'         => $amzCredentails['associate_tag'],
                    'pro_api_secret_key'    => $amzCredentails['pro_api_secret_key'],
                    'pro_api_access_key_id' => $amzCredentails['pro_api_access_key_id'],
                    'currency_code'         => $amzCredentails['currency_code'],
                    'country'               => $amzCredentails['country'],
                    'revise_item'           => $amzCredentails['revise_item'],
                    'del_from_catalog'      => $amzCredentails['del_from_catalog'],
                    'all_images'            => $amzCredentails['all_images'],
                    'default_qty'           => $amzCredentails['default_qty'],
                    'default_weight'        => $amzCredentails['default_weight'],
                    'shipped_order'         => $amzCredentails['shipped_order'],
                    'unshipped_order'       => $amzCredentails['unshipped_order'],
                    'partiallyshipped_order'=> $amzCredentails['partiallyshipped_order'],
                    'price_rule'            => $amzCredentails['price_rule'],
                    'export_image'            => $amzCredentails['export_image'],
                ];
                $this->amzClient = new MwsClient($this->config);
            }
            return $this->amzClient;
        } catch (\Exception $e) {
            $this->logger->info('Data getAmzClient : '.$e->getMessage());
            return false;
        }
    }

    /**
     * get amazon credentials by seller id
     *
     * @param int $sellerId
     * @return void
     */
    public function getSellerAmzCredentials($needObject = false)
    {
        if ($needObject || !$this->config) {
            return $this->accountsRepository->getCollectionById($this->accountId);
        } else {
            return $this->config;
        }
    }

    /**
     * get amazon account id from collection
     *
     * @param object $collection
     * @return void | int
     */
    public function getAmazonAccountId($collection)
    {
        $accountId = false;
        if ($collection->getSize()) {
            foreach ($collection as $account) {
                $accountId = $account->getMageAmzAccountId();
            }
        }
        return $accountId;
    }

    /**
     * get single record from collection
     *
     * @return object | void
     */
    public function getRecordModel($collection)
    {
        $recordModel = false;
        if ($collection->getSize()) {
            foreach ($collection as $record) {
                $recordModel = $record;
            }
        }
        return $recordModel;
    }

    /**
     * get amazon price rule by price
     *
     * @param integer $price
     * @return object
     */
    public function getPriceRuleByPrice($price)
    {
        if ($this->getPriceRuleConfigValue() !== 'none') {
            $amzPriceRuleCol = $this->objectManager
                ->create('Webkul\AmazonMagentoConnect\Model\PriceRuleFactory')
                ->create()
                ->getCollection()
                ->addFieldToFilter('amz_account_id', ['eq' => $this->accountId])
                ->addFieldToFilter('price_from', ['lteq' => round($price)])
                ->addFieldToFilter('price_to', ['gteq' => round($price)])
                ->addFieldToFilter('status', ['eq' => 1]);
            if ($amzPriceRuleCol->getSize()) {
                return $amzPriceRuleCol->getFirstItem();
            }
        }
        return false;
    }

    /**
     * get price after applied price rule
     *
     * @param object $ruleData
     * @param int $price
     * @param string $process
     * @return void
     */
    public function getPriceAfterAppliedRule($ruleData, $price, $process)
    {
        try {
            if ($price) {
                if ($ruleData->getOperationType() === 'Fixed') {
                    $price = $this->getFixedPriceCalculation($ruleData, $price, $process);
                } else {
                    $price = $this->getPercentPriceCalculation($ruleData, $price, $process);
                }
            }
            return $price;
        } catch (\Exception $e) {
            $this->logger->info('Helper Data getPriceAfterAppliedRule : '.$e->getMessage());
        }
    }

    /**
     * done fixed price rule calcuation
     *
     * @param object $ruleData
     * @param int $price
     * @param string $process
     * @return int
     */
    public function getFixedPriceCalculation($ruleData, $price, $process)
    {
        try {
            if ($ruleData->getOperation() === 'Increase') {
                if ($process === $this->getPriceRuleConfigValue()) {
                    $price = $price + $ruleData->getPrice();
                } else {
                    $price = $price - $ruleData->getPrice();
                }
            } else {
                if ($process === $this->getPriceRuleConfigValue()) {
                    $price = $price - $ruleData->getPrice();
                } else {
                    $price = $price + $ruleData->getPrice();
                }
            }
            return $price;
        } catch (\Exception $e) {
            $this->logger->info('Helper Data getFixedPriceCalculation : '.$e->getMessage());
        }
    }

    /**
     * done percent price rule calcuation
     *
     * @param object $ruleData
     * @param int $price
     * @param string $process
     * @return int
     */
    public function getPercentPriceCalculation($ruleData, $price, $process)
    {
        try {
            $percentPrice = ($price * $ruleData->getPrice())/100;
            if ($ruleData->getOperation() === 'Increase') {
                if ($process === $this->getPriceRuleConfigValue()) {
                    $price = $price + $percentPrice;
                } else {
                    $price = $price - $percentPrice;
                }
            } else {
                if ($process === $this->getPriceRuleConfigValue()) {
                    $price = $price - $percentPrice;
                } else {
                    $price = $price + $percentPrice;
                }
            }
            return $price;
        } catch (\Exception $e) {
            $this->logger->info('Helper Data getPercentPriceCalculation : '.$e->getMessage());
        }
    }

    /**
     * getPictureUrl use for get magento product image url.
     * @param object $product
     * @return array
     */
    public function getPictureUrl($product)
    {
        $productImagesList = $product->getMediaGalleryImages();
        $pictureUrl = '';
        $count = 1;
        $defaultImage =  $product->getThumbnail();
        foreach ($productImagesList as $image) {
            if ($count < 12) {
                $file = $image->getFile();
                $galleryUrl = $image->getUrl();
                if ($file == $defaultImage) {
                    $pictureUrl = $image->getUrl();
                }
            }
        }
        return $pictureUrl;
    }
}
