<?php

namespace BoostMyShop\Erp\Block\Products\Renderer;

use Magento\Framework\DataObject;

class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        $url = $this->getUrl('*/*/edit', ['id' => $row->getId()]);
        return '<a href="'.$url .'">'.$row->getsku().'</a>';
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsku();
    }

}