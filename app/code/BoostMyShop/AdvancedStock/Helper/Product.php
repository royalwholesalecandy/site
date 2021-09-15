<?php

namespace BoostMyShop\AdvancedStock\Helper;

class Product
{
    protected $_warehouseCollectionFactory;
    protected $_warehouseItemFactory;
    protected $_router;
    protected $_websiteCollectionFactory;

    public function __construct(
       \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
       \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
       \BoostMyShop\AdvancedStock\Model\Router $router,
       \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
       \Magento\Indexer\Model\IndexerFactory $indexerFactory
    ) {
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_router = $router;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->indexerFactory = $indexerFactory;
    }


    public function Fix($productId)
    {
        $allWarehouse = $this->_warehouseCollectionFactory->create();
        foreach($allWarehouse as $warehouse)
        {
            $warehouseId = $warehouse->getId();
            $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
            if($warehouseItem->getId()){
                $warehouseItem->updatePhysicalQuantity(true);
                $this->_router->updateQuantityToShip($productId, $warehouseId);
                $this->_router->updateReservedQuantity($productId, $warehouseId);
                $this->_router->updateSalableQuantity($productId, $warehouseId);
            }

        }
        
        $websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();
        foreach($websiteIds as $websiteId) 
        {
            $this->_router->updateSalableQuantity($websiteId, $productId);
        }

        $indexerId = 'cataloginventory_stock';
        $indexer = $this->indexerFactory->create();
        $indexer->load($indexerId);
        $indexer->reindexRow($indexerId);

        return;
    }

}