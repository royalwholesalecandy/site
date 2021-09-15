<?php

namespace BoostMyShop\AdvancedStock\Block\StockMovement\Renderer;

use Magento\Framework\DataObject;

class Direction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $image = '';
        if (!$row->getsm_from_warehouse_id())
            $image = 'increase.png';
        if (!$row->getsm_to_warehouse_id())
            $image = 'decrease.png';

        if ($image)
            return '<img src="'.$this->getViewFileUrl('BoostMyShop_AdvancedStock::images/'.$image).'" />';

    }
}