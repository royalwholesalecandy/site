<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class PackQty extends AbstractRenderer
{

    public function render(DataObject $row)
    {
    	
        $html = '<input size="6"
                        type="textbox"
                        name="products['.$row->getId().'][qty_pack]"
                        id="products['.$row->getId().'][qty_pack]"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_qty_pack\', this.value)"
                        value="'.$row->getpop_qty_pack().'">';

        return $html;
    }
}