<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Discount extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $value = number_format($row->getpop_discount_percent(), 2, '.', '');
        $html = '<input size="8"
                        type="textbox"
                        name="products['.$row->getId().'][discount]"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_discount_percent\', this.value)"
                        id="products['.$row->getId().'][discount]"
                        value="'.$value.'">';

        return $html;
    }
}