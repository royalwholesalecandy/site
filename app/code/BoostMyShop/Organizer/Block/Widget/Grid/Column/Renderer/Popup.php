<?php

namespace BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class Popup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
    	$objType = "'".$row->geto_object_type()."'";
        return '<span onclick="organizer.organizerPopup('.$row->getId().','.$row->geto_object_id().','.$objType.')" id="save_button_'.$row->getId().'_'.$row->geto_object_id().'" class="data-grid-row-changed" style="opacity: 1.5;"></span>';
    }

}