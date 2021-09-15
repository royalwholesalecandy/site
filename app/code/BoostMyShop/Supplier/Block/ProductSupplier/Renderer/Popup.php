<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class Popup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
    	if ($row->getsp_id()) {
	        return '<span onclick="objProductSupplier.popup('.$row->getsup_id().', '.$row->getentity_id().')" id="save_button_'.$row->getsup_id().'_'.$row->getentity_id().'" class="data-grid-row-changed" style="opacity: 1.5;"></span>';
	    }
    }

}