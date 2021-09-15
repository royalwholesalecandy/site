<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class LastBuyingPrice extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $value = '';
        if ($row->getProductSupplierAssociation() && $row->getProductSupplierAssociation()->getsp_last_buying_price())
            $value = $row->getOrder()->getCurrency()->format($row->getProductSupplierAssociation()->getsp_last_buying_price());
        return '<div align="right">'.$value.'</div>';
    }
}