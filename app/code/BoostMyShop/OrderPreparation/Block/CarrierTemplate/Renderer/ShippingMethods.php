<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class ShippingMethods extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $html = [];
        $methods = unserialize($row->getct_shipping_methods());
        foreach($methods as $method)
        {
            $html[] = $method;
        }
        return implode(', ', $html);
    }
}