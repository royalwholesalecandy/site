<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class PackQty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        return $row->getpop_qty_pack().'x';
    }
}