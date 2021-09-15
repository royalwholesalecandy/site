<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class TaxRate extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $value = number_format($row->getpop_tax_rate(), 2, '.', '');
        $html = '<input size="8"
                        type="textbox"
                        name="products['.$row->getId().'][tax_rate]"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_tax_rate\', this.value)"
                        id="products['.$row->getId().'][tax_rate]"
                        value="'.$value.'">';

        return $html;
    }
}