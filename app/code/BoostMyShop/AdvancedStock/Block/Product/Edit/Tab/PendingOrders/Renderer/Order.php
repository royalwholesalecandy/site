<?php

namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders\Renderer;

use Magento\Framework\DataObject;

class Order extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{


    public function render(DataObject $row)
    {
        $url = $this->getUrl('sales/order/view', ['order_id' => $row->getorder_id()]);
        return '<a href="'.$url .'">'.$row->getorder_increment_id().'</a>';
    }
}