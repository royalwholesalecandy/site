<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Po extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        $url = $this->getUrl('supplier/order/edit', ['po_id' => $row->getpo_id()]);
        return '<a href="'.$url .'">'.$row->getpo_reference().'</a>';
    }
}