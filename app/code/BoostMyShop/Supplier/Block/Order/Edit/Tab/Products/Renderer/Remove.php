<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Remove extends AbstractRenderer
{

    public function render(DataObject $row)
    {
        $html = '<input  type="checkbox" name="products['.$row->getId().'][remove]" id="products['.$row->getId().'][remove]" value="1">';

        return $html;
    }
}