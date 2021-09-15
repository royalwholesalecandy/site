<?php

namespace BoostMyShop\OrderPreparation\Block\Shipping\Renderer;

use Magento\Framework\DataObject;

class Shipment extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $inProgress)
    {
        if ($inProgress->getShipment())
            return '<a href="'.$this->getUrl('sales/shipment/view', ['shipment_id' => $inProgress->getShipment()->getId()]).'">'.$inProgress->getShipment()->getincrement_id().'</a>';
    }
}