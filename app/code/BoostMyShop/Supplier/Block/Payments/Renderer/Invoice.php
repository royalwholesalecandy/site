<?php

namespace BoostMyShop\Supplier\Block\Payments\Renderer;


class Invoice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $invoice_id = $row->getbsip_invoice_id();
        $url = $this->getUrl('supplier/invoice/edit/', ['bsi_id' => $invoice_id]);
        $html = '<a href="'.$url.'">'.$row->getbsi_reference().'</a>';
        return $html;
    }

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        return $row->getbsi_reference();
    }

}