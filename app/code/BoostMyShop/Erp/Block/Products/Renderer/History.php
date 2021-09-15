<?php

namespace BoostMyShop\Erp\Block\Products\Renderer;

use Magento\Framework\DataObject;

class History extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row)
    {
        return 'X';
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsku();
    }

}