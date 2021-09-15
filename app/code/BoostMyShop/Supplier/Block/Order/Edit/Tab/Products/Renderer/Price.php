<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Price extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $value = number_format($row->getpop_price(), 2, '.', '');
        $html = '<input size="8"
                        type="textbox"
                        name="products['.$row->getId().'][price]"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_price\', this.value)"
                        id="products['.$row->getId().'][price]"
                        value="'.$value.'">';

        return $html;
    }
}