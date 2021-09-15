<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Helper;


use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Stock extends \Magento\CatalogInventory\Helper\Stock
{
    private $stockRegistryProvider;
    private $_logger;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StatusFactory $stockStatusFactory,
        StockRegistryProviderInterface $stockRegistryProvider,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->stockStatusFactory  = $stockStatusFactory;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->_logger = $logger;
    }

    public function assignStatusToProduct(\Magento\Catalog\Model\Product $product, $status = null)
    {
        if ($status === null) {

            $websiteId = $product->getStore()->getWebsite()->getId();   //use website from product
            //$websiteId = $this->getStockConfiguration()->getDefaultScopeId();

            $stockStatus = $this->stockRegistryProvider->getStockStatus($product->getId(), $websiteId);
            $status = $stockStatus->getStockStatus();

            $this->_logger->log('assignStatusToProduct #'.$product->getId().' for website #'.$websiteId.' : '.($status ? 'salable' : 'not salable'), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
        }
        else
            $this->_logger->log('assignStatusToProduct #'.$product->getId().' : '.($status ? 'salable' : 'not salable'));
        $product->setIsSalable($status);
    }


    public function addInStockFilterToCollection($collection)
    {
        $manageStock = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $cond = [
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
        ];

        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = 1';
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $collection->joinField(
            'inventory_in_stock',
            'cataloginventory_stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '((' . join(') OR (', $cond) . ')) and at_inventory_in_stock.website_id='.$websiteId
        );

        $this->_logger->log('addInStockFilterToCollection for website #'.$websiteId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
    }

}