<?php

namespace BoostMyShop\Margin\Block\Widget\Grid\Column\Renderer\Cogs;

use Magento\Framework\DataObject;

class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store
{

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        $content = parent::renderExport($row);
        $content = str_replace(str_repeat(' ', 6), " ", $content);
        $content = str_replace(str_repeat(' ', 3), " ", $content);
        $content = str_replace("\r\n", " ", $content);
        return $content;
    }

}