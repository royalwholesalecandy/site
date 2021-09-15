<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse\Item;


class ReservationFixer
{
    const kOverReservation = 'over_reservation';
    const kSubReservation = 'sub_reservation';
    const kUnconsistent = 'unconsistent';
    const kUnknown = 'unknown';
    const kLocked = 'locked';

    protected $_pendingOrdersCollectionFactory;
    protected $_extendedOrderItemFactory;
    protected $_router;
    protected $_logger;
    protected $_config;

    protected static $_locks = [];

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrdersCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ExtendedSalesFlatOrderItemFactory $extendedOrderItemFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    ){
        $this->_pendingOrdersCollectionFactory = $pendingOrdersCollectionFactory;
        $this->_extendedOrderItemFactory = $extendedOrderItemFactory;
        $this->_router = $router;
        $this->_logger = $logger;
        $this->_config = $config;
    }

    public function reservationIsAllowed($status)
    {
        $allowedStatuses = $this->_config->getPendingOrderStatuses();
        $result = in_array($status, $allowedStatuses);
        $this->_logger->log("Reservation is ".($result ? '' : ' NOT ')." allowed for status  ".$status, \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);
        return $result;
    }

    public function fixForWarehouseItem($warehouseItem)
    {

        $status = $this->getStatus($warehouseItem);
        $this->_logger->log('Reservation status for product #'.$warehouseItem->getwi_product_id().' and warehouse #'.$warehouseItem->getwi_warehouse_id().' is : '.$status, \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);
        switch($status)
        {
            case self::kOverReservation:
                $this->processOverReservation($warehouseItem);
                break;
            case self::kSubReservation:
                $this->processSubReservation($warehouseItem);
                break;
            case self::kUnconsistent:
                //throw new \Exception('Unsupported unconsistent status for reservation fixer for warehouse item #'.$warehouseItem->getId());
                break;
            case self::kUnknown:
                throw new \Exception('Unsupported unknown status for reservation fixer for warehouse item #'.$warehouseItem->getId());
                break;
            case self::kLocked:
                return $this;
                break;
        }

        $this->unLock($warehouseItem);

        return $this;
    }

    protected function getStatus($warehouseItem)
    {
        $this->_logger->log('Calculate reservation status for warehouse item #'.$warehouseItem->getwi_id().' : reserved='.$warehouseItem->getwi_reserved_quantity().', physical='.$warehouseItem->getwi_physical_quantity().', toship= '.$warehouseItem->getwi_quantity_to_ship(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

        if (!$this->lock($warehouseItem))
            return self::kLocked;
        if ($warehouseItem->getwi_reserved_quantity() > $warehouseItem->getwi_physical_quantity())
            return self::kOverReservation;
        if ($warehouseItem->getwi_reserved_quantity() > $warehouseItem->getwi_quantity_to_ship())
            return self::kUnconsistent;
        if (($warehouseItem->getwi_reserved_quantity() < $warehouseItem->getwi_quantity_to_ship()) && ($warehouseItem->getwi_reserved_quantity() <= $warehouseItem->getwi_physical_quantity()))
            return self::kSubReservation;
        return self::kUnknown;
    }

    protected function processSubReservation($warehouseItem)
    {
        $reservableQuantity = $warehouseItem->getReservableQuantity();

        $this->_logger->log('Process Sub Reservation for product #'.$warehouseItem->getwi_product_id().' and warehouse #'.$warehouseItem->getwi_warehouse_id().' : can reserve up to '.$reservableQuantity.' products', \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

        $pendingOrders = $this->getPendingOrderItems($warehouseItem);
        $this->_logger->log('Process Sub Reservation for product #'.$warehouseItem->getwi_product_id().' and warehouse #'.$warehouseItem->getwi_warehouse_id().' : '.count($pendingOrders).' pending orders', \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);
        foreach($pendingOrders as $orderItem)
        {
            $qtyToShip = $orderItem->getSimpleQtyToShip();
            $qtyReserved = $orderItem->getesfoi_qty_reserved();
            $qtyToReserve = ($qtyToShip - $qtyReserved);
            $qtyToReserve = min($qtyToReserve, $reservableQuantity);

            $this->_logger->log('Process Sub Reservation for order #'.$orderItem->getorder_id().' : qtytoship is '.$qtyToShip.', qtytoreserve is '.$qtyToReserve, \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

            if ($qtyToReserve > 0)
                $this->reserveQuantity($orderItem->getId(), $qtyToReserve);

            $reservableQuantity -= $qtyToReserve;
        }
    }

    protected function processOverReservation($warehouseItem)
    {
        $maxReservableQty = min($warehouseItem->getwi_physical_quantity(), $warehouseItem->getwi_quantity_to_ship());
        $totalQtyToRelease = $warehouseItem->getwi_reserved_quantity() - $maxReservableQty;
        if ($totalQtyToRelease > 0)
        {
            $this->_logger->log('Process Over Reservation for product #'.$warehouseItem->getwi_product_id().' and warehouse #'.$warehouseItem->getwi_warehouse_id().' : need to release '.$totalQtyToRelease.' products', \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

            foreach($this->getPendingOrderItems($warehouseItem, 'DESC') as $orderItem)
            {
                $qtyToRelease = min($orderItem->getesfoi_qty_reserved(), $totalQtyToRelease);
                if ($qtyToRelease > 0)
                {
                    $this->releaseQuantity($orderItem->getId(), $qtyToRelease);
                    $totalQtyToRelease -= $qtyToRelease;
                }
            }
        }

    }

    public function reserveQuantity($orderItemId, $qtyToReserve)
    {
        $extendedOrderItem = $this->_extendedOrderItemFactory->create()->loadByItemId($orderItemId);
        $warehouseId = $extendedOrderItem->getesfoi_warehouse_id();
        $productId = $extendedOrderItem->getOrderItem()->getproduct_id();

        $totalReservedQty = $extendedOrderItem->getesfoi_qty_reserved() + $qtyToReserve;

        $this->_logger->log('Reserve '.$totalReservedQty.'x items for product '.$productId.' and order #'.$extendedOrderItem->getOrderItem()->getorder_id(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

        if ($totalReservedQty < 0)
            throw new \Exception('Impossible to reserve a negative quantity');

        $extendedOrderItem->setesfoi_qty_reserved($totalReservedQty)->save();

        $this->_router->updateReservedQuantity($productId, $warehouseId);

        return $this;
    }

    public function releaseQuantity($orderItemId, $qtyToRelease)
    {
        $extendedOrderItem = $this->_extendedOrderItemFactory->create()->loadByItemId($orderItemId);
        $warehouseId = $extendedOrderItem->getesfoi_warehouse_id();
        $productId = $extendedOrderItem->getOrderItem()->getproduct_id();

        $this->_logger->log('Release '.$qtyToRelease.'x items for product '.$productId.' and order #'.$extendedOrderItem->getOrderItem()->getorder_id(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);

        $totalReservedQty = $extendedOrderItem->getesfoi_qty_reserved() - $qtyToRelease;

        if ($totalReservedQty < 0)
            throw new \Exception('Impossible to reserve a negative quantity');

        $extendedOrderItem->setesfoi_qty_reserved($totalReservedQty)->save();

        $this->_router->updateReservedQuantity($productId, $warehouseId);

        return $this;
    }

    protected function getPendingOrderItems($warehouseItem, $sort = 'ASC')
    {
        $productId = $warehouseItem->getwi_product_id();
        $warehouseId = $warehouseItem->getwi_warehouse_id();

        return $this->_pendingOrdersCollectionFactory
                            ->create()
                            ->addProductFilter($productId)
                            ->addWarehouseFilter($warehouseId)
                            ->addExtendedDetails()
                            ->addStatusesFilter($this->_config->getPendingOrderStatuses())
                            ->addOrderDetails()
                            ->setOrder('item_id', $sort);
    }

    protected function lock($warehouseItem)
    {
        if (isset(self::$_locks[$warehouseItem->getId()]))
            return false;
        self::$_locks[$warehouseItem->getId()] = true;
        return true;
    }

    protected function unLock($warehouseItem)
    {
        if (isset(self::$_locks[$warehouseItem->getId()]))
            unset(self::$_locks[$warehouseItem->getId()]);
    }
}