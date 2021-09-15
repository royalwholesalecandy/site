<?php

namespace BoostMyShop\Supplier\Block\Supplier\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class OpenedOrder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $supplier)
    {
        return $supplier->getOpenedPoCount();
    }
}