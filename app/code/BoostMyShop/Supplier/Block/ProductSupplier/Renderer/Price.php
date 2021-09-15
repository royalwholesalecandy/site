<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class Price extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'products['.$row->getsup_id().']['.$row->getentity_id().'][sp_price]';
            return '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$row->getsp_price().'"> ';
        }
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsp_price();
    }

}