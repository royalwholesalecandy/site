<?php namespace BoostMyShop\Supplier\Block\Order\Grid\Renderer;

use Magento\Framework\DataObject;

class Invoice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_invoiceFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\InvoiceFactory $invoiceFactory,
        array $data = []
    )
    {
        $this->_invoiceFactory = $invoiceFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function render(DataObject $row)
    {
        /** @var  \BoostMyShop\Supplier\Model\Order $row */
        $invoices = $row->getInvoices();
        $html = '';
        foreach($invoices as $invoice){
            $html .= '<a href="'. $this->getInvoiceUrl($invoice) .'">'. $invoice->getbsi_reference() .'</a><br />';
        }
        return $html;
    }

    /**
     * @param $invoice \BoostMyShop\Supplier\Model\Invoice
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInvoiceUrl($invoice)
    {
        return $this->getUrl('supplier/invoice/edit', ['bsi_id' => $invoice->getbsi_id()]);
    }

}
