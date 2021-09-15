<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        if ($row->getsp_id())
        {
            $name = 'products['.$row->getsp_id().'][price]';
            return '<input type="text" onchange="supplier.logChange('.$row->getsp_id().', \'price\', this.value);" name="'.$name.'" id="'.$name.'" value="'.$row->getsp_price().'">';
        }
    }
}