<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class SupplierSku extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $html = '<input size="12"
                        type="textbox"
                        name="products['.$row->getId().'][supplier_sku]"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_supplier_sku\', this.value)"
                        id="products['.$row->getId().'][supplier_sku]"
                        value="'.$row->getpop_supplier_sku().'">';

        return $html;
    }
}