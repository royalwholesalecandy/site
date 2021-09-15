<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Renderer;

use Magento\Framework\DataObject;

class PrintReception extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $url = $this->getUrl('*/*/printReception', ['id' => $row->getId()]);
        $html = '<a href="'.$url.'">'.__('Print').'</a>';
        return $html;
    }
}