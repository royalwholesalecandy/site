<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit\Tab\Payments\Renderer;

use Magento\Framework\DataObject;

class Remove extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $html = '<input  type="checkbox" name="paymentsGrid['.$row->getId().'][remove]" id="paymentsGrid['.$row->getId().'][remove]" value="1">';

        return $html;
    }
}