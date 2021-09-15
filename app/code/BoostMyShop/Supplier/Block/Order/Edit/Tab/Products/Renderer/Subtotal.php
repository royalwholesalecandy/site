<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Subtotal extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $value = $row->getOrder()->getCurrency()->format($row->getSubTotal(), [], false);
        return '<div align="right">'.$value.'</div>';
    }
}