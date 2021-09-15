<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier\Renderer;

use Magento\Framework\DataObject;

class Primary extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        if ($row->getsp_id()) {
            $name = 'products[' . $row->getsup_id() . '][' . $row->getentity_id() . '][sp_primary]';
            $html = '<select  name="' . $name . '" id="' . $name . '">';
            $html .= '<option value="0" ' . ($row->getsp_primary() ? '' : ' selected ') . '>No</option>';
            $html .= '<option value="1" ' . ($row->getsp_primary() ? ' selected ' : '') . '>Yes</option>';
            $html .= '</select>';
            return $html;
        }
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsp_primary();
    }

}