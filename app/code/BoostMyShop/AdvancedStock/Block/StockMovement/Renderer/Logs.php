<?php

namespace BoostMyShop\AdvancedStock\Block\StockMovement\Renderer;

use Magento\Framework\DataObject;

class Logs extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $url = $this->getUrl('advancedstock/stockmovement/downloadsm', ['sm_id' => $row->getsm_id()]);


        return '<a href="'.$url.'">'.__('Download').'</a>';
    }
}