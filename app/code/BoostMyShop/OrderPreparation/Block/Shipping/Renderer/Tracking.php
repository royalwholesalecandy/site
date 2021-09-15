<?php

namespace BoostMyShop\OrderPreparation\Block\Shipping\Renderer;

use Magento\Framework\DataObject;

class Tracking extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $inProgress)
    {
        if ($inProgress->getShipment())
            $html = '<input type="text" name="tracking['.$inProgress->getId().']" value="'.$inProgress->getTrackingNumber().'">';
        else
            $html = __('This order is not shipped yet');
        return $html;
    }
}