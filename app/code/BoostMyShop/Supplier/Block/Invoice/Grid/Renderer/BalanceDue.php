<?php

namespace BoostMyShop\Supplier\Block\Invoice\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class BalanceDue extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
    	$balnceDue = $row->getBsiTotal() - $row->getBsiTotalPaid();
        return $row->getCurrency()->format($balnceDue, [], false);
    }
}