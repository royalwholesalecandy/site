<?php

namespace BoostMyShop\Supplier\Block\Payments\Renderer;


class Comments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        return strip_tags($row->getbsip_notes());
    }

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($row->getbsip_notes())));
    }

}