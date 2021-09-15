<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class PackQty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'products['.$row->getsup_id().']['.$row->getentity_id().'][sp_pack_qty]';
            return '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$row->getsp_pack_qty().'">';
        }
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsp_pack_qty();
    }

}