<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class StockMovementAfterSave implements ObserverInterface
{

    protected $_warehouseItemFactory;
    protected $_stockMovementFactory;
    protected $_backendAuthSession;
    protected $_logger;

    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    ) {
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_logger = $logger;
    }

    /**
     * Saving product inventory data. Product qty calculated dynamically.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $stockMovement = $observer->getEvent()->getObject();

        $productId = $stockMovement->getsm_product_id();
        $warehouses = [];
        if ($stockMovement->getsm_to_warehouse_id())
            $warehouses[] = $stockMovement->getsm_to_warehouse_id();
        if ($stockMovement->getsm_from_warehouse_id())
            $warehouses[] = $stockMovement->getsm_from_warehouse_id();

        foreach($warehouses as $warehouseId)
        {
            $this->_logger->log('Update physical quantity for product #'.$productId.' in warehouse #'.$warehouseId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);

            $stockItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
            $stockItem->updatePhysicalQuantity(true);
        }

        return $this;
    }

}
