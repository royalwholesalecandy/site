<?php

namespace BoostMyShop\AdvancedStock\Block\MassStockEditor\Renderer;

use Magento\Framework\DataObject;

class ShelfLocation extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        $name = 'massstockeditor['.$row->getmse_id().'][wi_shelf_location]';
        return '<input type="text" onchange="massStockEditor.logChange(\''.$row->getmse_id().'\', \'wi_shelf_location\', this.value);" name="'.$name.'" id="'.$name.'" value="'.$row->getwi_shelf_location().'" size="10">';
    }

    public function renderExport(DataObject $row)
    {
        return $row->getwi_shelf_location();
    }

}