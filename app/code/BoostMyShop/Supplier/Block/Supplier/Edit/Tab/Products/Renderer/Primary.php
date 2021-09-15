<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Primary extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'products[' . $row->getsp_id() . '][primary]';
            $html = '<select  name="' . $name . '" id="' . $name . '" onchange="supplier.logChange(' . $row->getsp_id() . ', \'primary\', this.value);">';
            $html .= '<option value="0" ' . ($row->getsp_primary() ? '' : ' selected="selected" ') . '>No</option>';
            $html .= '<option value="1" ' . ($row->getsp_primary() ? ' selected="selected" ' : '') . '>Yes</option>';
            $html .= '</select>';

            return $html;
        }
    }
}