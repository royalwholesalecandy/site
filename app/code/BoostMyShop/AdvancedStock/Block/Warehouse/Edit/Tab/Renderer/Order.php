<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Order extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $url = $this->getUrl('sales/order/view', ['order_id' => $row->getOrderId(), 'active_tab' => 'product-advancedstock']);
        return '<a href="'.$url .'">'.$row->getorder_increment_id().'</a>';
    }

    public function renderExport(DataObject $row)
    {
        return $row->getorder_increment_id();
    }

}