<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        return '<button id="save_button_'.$row->getsup_id().'_'.$row->getentity_id().'" style="display: none;" type="button" class="action-default scalable save primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="ui-button-text" onclick="objProductSupplier.save('.$row->getsup_id().', '.$row->getentity_id().')">
                    <span>Save</span>
                </span></button>';
    }

}