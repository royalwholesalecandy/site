<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SalesOrderSaveAfter implements ObserverInterface
{

    protected $_logger;
    protected $_router;
    protected $_reservationFixer;
    protected $_extendedOrderItemFactory;
    protected $_warehouseItemFactory;

    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedOrderItemFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\Item\ReservationFixer $reservationFixer

    ) {
        $this->_logger = $logger;
        $this->_router = $router;
        $this->_reservationFixer = $reservationFixer;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_extendedOrderItemFactory = $extendedOrderItemFactory;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->getStatus() != $order->getOrigData('status'))
        {
            $this->_logger->log("Order #".$order->getIncrementId()." status changes from ".$order->getOrigData('status')." to ".$order->getStatus(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

            $reservationAllowedBefore = $this->_reservationFixer->reservationIsAllowed($order->getOrigData('status'));
            $reservationAllowedAfter = $this->_reservationFixer->reservationIsAllowed($order->getStatus());

            if ($reservationAllowedAfter != $reservationAllowedBefore)
            {
                $this->_logger->log("Order #".$order->getIncrementId()." reservation status changes, update reservation for items ", \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);
                foreach($order->getAllItems() as $orderItem)
                {
                    $this->processOrderItem($orderItem, $reservationAllowedAfter);
                }
            }
        }
    }

    protected function processOrderItem($orderItem, $reservationAllowed)
    {
        $extendedOrderItem = $this->_extendedOrderItemFactory->create()->loadByItemId($orderItem->getId());
        $warehouseId = $extendedOrderItem->getesfoi_warehouse_id();
        if (!$warehouseId)
            return;

        if (!$reservationAllowed)
        {
            $qtyToRelease = $extendedOrderItem->getesfoi_qty_reserved();
            if ($qtyToRelease > 0)
                $this->_reservationFixer->releaseQuantity($orderItem->getId(), $qtyToRelease);
        }
        else
        {
            $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($orderItem->getProductId(), $warehouseId);
            $reservableQuantity = $warehouseItem->getReservableQuantity();
            $reservableQuantity = min($reservableQuantity, $extendedOrderItem->getQuantityToShip());

            $extendedOrderItem->setesfoi_qty_reserved($reservableQuantity)->save();
        }

        $this->updateWarehouseItem($warehouseId, $orderItem->getProductId());
    }

    protected function updateWarehouseItem($warehouseId, $productId)
    {
        $this->_router->updateQuantityToShip($productId, $warehouseId);
        $this->_router->updateReservedQuantity($productId, $warehouseId);
    }

}
