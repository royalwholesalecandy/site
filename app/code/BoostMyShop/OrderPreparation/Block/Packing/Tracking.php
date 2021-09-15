<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Tracking extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Tracking.phtml';

    public function canDisplay()
    {
        return ($this->hasOrderSelect()
            && $this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED
        );
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/submitTracking', ['order_id' => $this->currentOrderInProgress()->getId()]);
    }


    public function getTrackingNumber()
    {
        if ($this->currentOrderInProgress()->getShipment())
        {
            foreach($this->currentOrderInProgress()->getShipment()->getTracksCollection() as $tracking)
            {
                return $tracking->getNumber();
            }
        }

        return '';
    }

}