<?php

namespace BoostMyShop\OrderPreparation\Model\InProgress;


class Invoice
{
    protected $_invoiceService;
    protected $_invoiceSender;
    protected $_transaction;
    protected $_registry;

    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        $this->_invoiceSender = $invoiceSender;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_registry = $registry;
    }

    /**
     * @param $inProgress
     */
    public function createInvoice($inProgress, $invoiceItems = null)
    {
        $order = $inProgress->getOrder();

        if ($invoiceItems == null)
            $invoiceItems = $this->prepareInvoiceItems($inProgress);

        $this->appendParents($inProgress, $invoiceItems);

        $invoice = $this->_invoiceService->prepareInvoice($order, $invoiceItems);
        if ($invoice->canCapture())
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);

        $commentText = 'Packed by '.$inProgress->getOperatorName();
        $invoice->addComment(
            $commentText,
            false,
            false
        );
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());

        $transactionSave->save();
        $this->_invoiceSender->send($invoice);

        return $invoice;
    }

    /**
     *
     * @param $inProgress
     * @return array
     */
    protected function prepareInvoiceItems($inProgress)
    {
        $items = [];

        foreach($inProgress->getAllItems() as $item)
        {
            $items[$item->getitem_id()] = $item->getipi_qty();
        }

        return $items;
    }

    protected function appendParents($inProgress, &$invoiceItems)
    {
        foreach($inProgress->getAllItems() as $item)
        {
            if (isset($invoiceItems[$item->getitem_id()]) && ($invoiceItems[$item->getitem_id()] > 0))
            {
                if ($item->shipWithParent() && (!isset($invoiceItems[$item->getOrderItem()->getParentItemId()])))
                    $invoiceItems[$item->getOrderItem()->getParentItemId()] = $item->getipi_qty();

            }
        }
    }

}