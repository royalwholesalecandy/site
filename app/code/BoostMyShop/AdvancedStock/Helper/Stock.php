<?php

namespace BoostMyShop\AdvancedStock\Helper;

class Stock
{
    protected $_websiteCollectionFactory;
    protected $_stockCollectionFactory;
    protected $_stockFactory;

    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\CatalogInventory\Model\StockFactory $stockFactory
    ) {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_stockCollectionFactory = $stockCollectionFactory;
        $this->_stockFactory = $stockCollectionFactory;
    }



}