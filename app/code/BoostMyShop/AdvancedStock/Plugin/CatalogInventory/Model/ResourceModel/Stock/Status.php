<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Status extends \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
{
    protected $_advancedStocklogger;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        $connectionName = null
    ) {
        parent::__construct($context, $storeManager, $websiteFactory, $eavConfig, $connectionName);

        $this->_advancedStocklogger = $logger;
    }

    public function addStockStatusToSelect(\Magento\Framework\DB\Select $select, \Magento\Store\Model\Website $website)
    {
        //$websiteId = $this->getStockConfiguration()->getDefaultScopeId(); <-- Magento code
        $websiteId = $website->getId();

        $select->joinLeft(
            ['stock_status' => $this->getMainTable()],
            'e.entity_id = stock_status.product_id AND stock_status.website_id=' . $websiteId,
            ['is_salable' => 'stock_status.stock_status']
        );

        $this->_advancedStocklogger->log('Status::addStockStatusToSelect for websiteId = '.$websiteId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);

        return $this;
    }


    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        //$websiteId = $this->getStockConfiguration()->getDefaultScopeId(); <-- old code
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $joinCondition = $this->getConnection()->quoteInto(
            'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        /*
        $joinCondition .= $this->getConnection()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            Stock::DEFAULT_STOCK_ID
        );
        */

        $method = $isFilterInStock ? 'join' : 'joinLeft';
        $collection->getSelect()->$method(
            ['stock_status_index' => $this->getMainTable()],
            $joinCondition,
            ['is_salable' => 'stock_status']
        );

        if ($isFilterInStock) {
            $collection->getSelect()->where(
                'stock_status_index.stock_status = ?',
                Stock\Status::STATUS_IN_STOCK
            );
        }

        $this->_advancedStocklogger->log('Status::addStockDataToCollection for websiteId = '.$websiteId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);

        return $collection;
    }


    public function addIsInStockFilterToCollection($collection)
    {
        //$websiteId = $this->getStockConfiguration()->getDefaultScopeId(); <-- old code
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $joinCondition = $this->getConnection()->quoteInto(
            'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        /*
        $joinCondition .= $this->getConnection()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            Stock::DEFAULT_STOCK_ID
        );
        */

        $collection->getSelect()->join(
            ['stock_status_index' => $this->getMainTable()],
            $joinCondition,
            []
        )->where(
            'stock_status_index.stock_status=?',
            Stock\Status::STATUS_IN_STOCK
        );

        $this->_advancedStocklogger->log('Status::addIsInStockFilterToCollection for websiteId = '.$websiteId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);

        return $this;
    }


}
