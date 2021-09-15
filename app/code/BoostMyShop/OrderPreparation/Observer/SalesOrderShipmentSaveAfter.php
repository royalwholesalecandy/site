<?php

namespace BoostMyShop\OrderPreparation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;


class SalesOrderShipmentSaveAfter implements ObserverInterface
{
    protected $_inProgressFactory;
    protected $_orderPreparationRegistry;

    public function __construct(
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $orderPreparationRegistry
    ) {
        $this->_inProgressFactory = $inProgressFactory;
        $this->_orderPreparationRegistry = $orderPreparationRegistry;

    }

    public function execute(EventObserver $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $orderId = $shipment->getOrderId();

        $warehouseId = $this->_orderPreparationRegistry->getCurrentWarehouseId();
        $operatorId = $this->_orderPreparationRegistry->getCurrentOperatorId();
        $inProgress = $this->_inProgressFactory->create()->loadFromOrderIdAndContext($orderId, $warehouseId, $operatorId);

        if ($inProgress->getId()) {
            $inProgress->setip_shipment_id($shipment->getId())->save();
            $inProgress->changeStatus(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED);
        }

        return $this;
    }

}
