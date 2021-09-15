<?php

namespace BoostMyShop\Supplier\Block\Order\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Total extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        return $row->getCurrency()->format($row->getpo_grandtotal(), [], false);

    }
}