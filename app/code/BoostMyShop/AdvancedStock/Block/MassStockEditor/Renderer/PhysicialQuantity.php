<?php

namespace BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer;

use Magento\Framework\DataObject;

class PhysicialQuantity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        $name = 'massstockeditor['.$row->getmse_id().'][wi_physical_quantity]';
        return '<input type="text" onchange="massStockEditor.logChange(\''.$row->getmse_id().'\', \'wi_physical_quantity\', this.value);" name="'.$name.'" id="'.$name.'" value="'.$row->getwi_physical_quantity().'" size="5">';
    }

    public function renderExport(DataObject $row)
    {
        return $row->getwi_physical_quantity();
    }

}