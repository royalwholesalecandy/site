<?php

namespace BoostMyShop\OrderPreparation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;


class SalesOrderShipmentTrackSaveAfter implements ObserverInterface
{
    protected $_inProgressFactory;

    public function __construct(
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory
    ) {
        $this->_inProgressFactory = $inProgressFactory;

    }

    public function execute(EventObserver $observer)
    {
        $track = $observer->getEvent()->gettrack();
        $shipmentId = $track->getparent_id();

        $inProgress = $this->_inProgressFactory->create()->load($shipmentId, 'ip_shipment_id');
        if ($inProgress->getId())
            $inProgress->changeStatus(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED);

        return $this;
    }

}
