<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class Moq extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'products['.$row->getsup_id().']['.$row->getentity_id().'][sp_moq]';
            return '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$row->getsp_moq().'">';
        }
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsp_moq();
    }

}