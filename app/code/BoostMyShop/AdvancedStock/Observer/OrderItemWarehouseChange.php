<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OrderItemWarehouseChange implements ObserverInterface
{
    protected $_router;
    protected $_config;
    protected $_logger;
    protected $_warehouseItemFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \BoostMyShop\AdvancedStock\Model\Warehouse\Item\ReservationFixer $reservationFixer
    ) {
        $this->_backendAuthSession = $backendAuthSession;
        $this->_router = $router;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_logger = $logger;
        $this->_config = $config;
        $this->_reservationFixer = $reservationFixer;
    }


    public function execute(EventObserver $observer)
    {
        $extendedOrderItem = $observer->getEvent()->getextended_item();
        $orderItem = $extendedOrderItem->getOrderItem();
        $oldWarehouseId = $observer->getEvent()->getold_warehouse_id();
        $newWarehouseId = $observer->getEvent()->getnew_warehouse_id();

        $this->_logger->log("Warehouse for order item #".$orderItem->getId()." changed from warehouse #".$oldWarehouseId." to #".$newWarehouseId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

        if ($oldWarehouseId)
            $this->updateWarehouseItem($oldWarehouseId, $orderItem->getProductId());

        $order = $orderItem->getOrder();
        if ($this->_reservationFixer->reservationIsAllowed($order->getStatus()))
            $this->reserve($orderItem, $extendedOrderItem, $newWarehouseId);

        $this->updateWarehouseItem($newWarehouseId, $orderItem->getProductId());

        return $this;
    }

    protected function reserve($orderItem, $extendedOrderItem, $warehouseId)
    {
        $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($orderItem->getProductId(), $warehouseId);

        $reservableQuantity = $warehouseItem->getReservableQuantity();
        $reservableQuantity = min($reservableQuantity, $extendedOrderItem->getQuantityToShip());

        $this->_logger->log("Reserved qty for order item #".$orderItem->getId()." is ".$reservableQuantity, \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);
        $extendedOrderItem->setesfoi_qty_reserved($reservableQuantity)->save();

    }

    protected function updateWarehouseItem($warehouseId, $productId)
    {
        $this->_router->updateQuantityToShip($productId, $warehouseId);
        $this->_router->updateReservedQuantity($productId, $warehouseId);
    }

}
