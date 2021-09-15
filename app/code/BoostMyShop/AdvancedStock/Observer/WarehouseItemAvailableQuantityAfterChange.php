<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class WarehouseItemAvailableQuantityAfterChange implements ObserverInterface
{
    protected $_router;

    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Router $router
    ) {
        $this->_router = $router;
    }

    public function execute(EventObserver $observer)
    {
        $warehouseItem = $observer->getEvent()->getWarehouseItem();

        //identify website impacted for the change of the available quantity
        $websiteIds = $this->_router->getWebsitesForSales($warehouseItem->getwi_warehouse_id());
        foreach($websiteIds as $websiteId)
        {
            $this->_router->updateSalableQuantity($websiteId, $warehouseItem->getwi_product_id());
        }

        return $this;
    }

}
