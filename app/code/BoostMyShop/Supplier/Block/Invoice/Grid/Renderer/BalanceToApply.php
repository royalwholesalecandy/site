<?php

namespace BoostMyShop\Supplier\Block\Invoice\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class BalanceToApply extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
    	$balnceToApply = $row->getBsiTotal() - $row->getBsiTotalApplied();
        return $row->getCurrency()->format($balnceToApply, [], false);
    }

}