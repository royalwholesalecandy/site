<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Confirm extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Confirm.phtml';

    public function canDisplay()
    {
        return ($this->hasOrderSelect()
                &&
                (
                    $this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED
                    ||
                    $this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED
                )
                );
    }

    public function getDownloadPackingSlipUrl()
    {
        if ($this->currentOrderInProgress()->getip_shipment_id())
        {
            return $this->getUrl('sales/shipment/print', ['shipment_id' => $this->currentOrderInProgress()->getip_shipment_id()]);
        }
    }

    public function getDownloadInvoiceUrl()
    {
        if ($this->currentOrderInProgress()->getip_invoice_id())
        {
            return $this->getUrl('sales/order_invoice/print', ['invoice_id' => $this->currentOrderInProgress()->getip_invoice_id()]);
        }
    }

    public function getDownloadPickingListUrl()
    {
        return $this->getUrl('*/*/download', ['document' => 'picking', 'order_id' => $this->currentOrderInProgress()->getId()]);
    }

    public function getDownloadShippingLabel()
    {
        $template = $this->_carrierTemplateHelper->getCarrierTemplateForOrder($this->currentOrderInProgress());
        if ($template)
        {
            return $this->getUrl('*/*/download', ['document' => 'shipping_label', 'order_id' => $this->currentOrderInProgress()->getId()]);
        }
    }

    public function autoDownload()
    {
        return ($this->getRequest()->getParam('download') == 1);
    }

    public function getDownloadUrlsAsJson()
    {
        $urls = [];

        if ($this->_config->getSetting('packing/download_invoice'))
            $urls[] = $this->getDownloadInvoiceUrl();
        if ($this->_config->getSetting('packing/download_shipment'))
            $urls[] = $this->getDownloadPackingSlipUrl();
        if ($this->_config->getSetting('packing/download_shipping_label'))
            $urls[] = $this->getDownloadShippingLabel();

        return json_encode($urls);
    }

}