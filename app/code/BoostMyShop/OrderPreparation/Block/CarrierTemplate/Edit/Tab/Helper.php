<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class Helper extends \Magento\Backend\Block\Template
{
    protected $_template = 'OrderPreparation/CarrierTemplate/Edit/Tab/Helper.phtml';

    protected $_inProgress;
    protected $_inProgressItem;

    protected $_orderCollectionFactory;
    protected $_orderInvoiceCollectionFactory;
    protected $_orderShipmentCollectionFactory;
    protected $_orderItemCollectionFactory;

    protected $_orderId = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \BoostMyShop\OrderPreparation\Model\InProgress $inProgress,
                                \BoostMyShop\OrderPreparation\Model\InProgress\Item $inProgressItem,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $orderInvoiceCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
                                array $data = [])
    {
        $this->_inProgress = $inProgress;
        $this->_inProgressItem = $inProgressItem;

        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderInvoiceCollectionFactory = $orderInvoiceCollectionFactory;
        $this->_orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;

        parent::__construct($context, $data);

    }


    protected function getDummyInProgress()
    {
        $orderId = $this->getOrderId();

        $data = [
            'ip_order_id'       => $orderId,
            'ip_store_id'       => 1,
            'ip_status'         => 'shipped',
            'ip_invoice_id'     => $this->getInvoiceId($orderId),
            'ip_shipment_id'    => $this->getShipmentId($orderId),
            'ip_weights'        => 5.2,
        ];
        $this->_inProgress->setData($data);

        return $this->_inProgress;
    }

    protected function getDummyInProgressItem()
    {
        $data = [
            'ipi_qty'               => 2,
            'ipi_order_item_id'     => $this->getOrderItemId($this->getOrderId())
        ];
        $this->_inProgressItem->setData($data);
        return $this->_inProgressItem;
    }

    protected function getOrderId()
    {
        if (!$this->_orderId)
            $this->_orderId = $this->_orderCollectionFactory->create()->setOrder('entity_id', 'desc')->addFieldToFilter('state', 'complete')->getFirstItem()->getId();
        return $this->_orderId;
    }

    protected function getInvoiceId($orderId)
    {
        $invoiceId = $this->_orderInvoiceCollectionFactory->create()->addFieldToFilter('order_id', $orderId)->setOrder('entity_id', 'desc')->getFirstItem()->getId();
        return $invoiceId;
    }

    protected function getShipmentId($orderId)
    {
        $shipmentId = $this->_orderShipmentCollectionFactory->create()->addFieldToFilter('order_id', $orderId)->setOrder('entity_id', 'desc')->getFirstItem()->getId();
        return $shipmentId;
    }

    protected function getOrderItemId($orderId)
    {
        $orderItemId = $this->_orderItemCollectionFactory->create()->addFieldToFilter('order_id', $orderId)->setOrder('item_id', 'desc')->getFirstItem()->getId();
        return $orderItemId;
    }


    public function getCodesForOrders()
    {
        return $this->getDummyInProgress()->getDatasForExport();
    }

    public function getCodesForOrderItems()
    {
        return $this->getDummyInProgressItem()->getDatasForExport();
    }

}
