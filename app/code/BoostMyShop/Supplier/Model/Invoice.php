<?php

namespace BoostMyShop\Supplier\Model;


class Invoice extends \Magento\Framework\Model\AbstractModel
{
    protected $_dateTime = null;

    protected $_invoiceOrderFactory = null;
    protected $_invOrderCollectionFactory = null;
    protected $_invPaymentsCollectionFactory = null;
    protected $_invoicePaymentsFactory = null;
    protected $_storeManager;
    protected $_currencyFactory;
    protected $_supplierFactory;
    protected $_supplier;
    protected $_manager;
    protected $_userFactory;
    protected $_config;
    protected $_currency;
    protected $_logger;
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Invoice');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \BoostMyShop\Supplier\Model\Invoice\OrderFactory $invoiceOrderFactory,
        \BoostMyShop\Supplier\Model\Invoice\PaymentsFactory $invoicePaymentsFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Order\CollectionFactory $invOrderCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Payments\CollectionFactory $invPaymentsCollectionFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \BoostMyShop\Supplier\Model\Config $config,
        \Magento\User\Model\User $userFactory,
        \BoostMyShop\Supplier\Helper\Logger $logger,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
        $this->_invoiceOrderFactory = $invoiceOrderFactory;
        $this->_invoicePaymentsFactory = $invoicePaymentsFactory;
        $this->_invOrderCollectionFactory = $invOrderCollectionFactory;
        $this->_invPaymentsCollectionFactory = $invPaymentsCollectionFactory;
        $this->_supplierFactory = $supplierFactory;
        $this->_currencyFactory = $currencyFactory;
        $this->_userFactory = $userFactory;
        $this->_config = $config;
        $this->_logger = $logger;
    }

    public function beforeDelete()
    {
        parent::beforeDelete();

        foreach($this->getAllOrderItems() as $item)
            $item->delete();

        foreach($this->getAllPaymentsItems() as $item)
            $item->delete();

        return $this;
    }

    public function getSupplier()
    {
        if (!$this->_supplier)
            $this->_supplier = $this->_supplierFactory->create()->load($this->getbsi_sup_id());
        return $this->_supplier;
    }

    public function getAllOrderItems()
    {
        $collection = $this->_invOrderCollectionFactory->create();
        $collection->addInvoiceFilter($this->getId());
        return $collection;
    }

    public function getAllPaymentsItems()
    {
        $collection = $this->_invPaymentsCollectionFactory->create();
        $collection->addInvoiceFilter($this->getId());
        return $collection;
    }

    public function linkOrder($orderId, $amount){
        $obj = $this->_invoiceOrderFactory->create();
        $obj->setbsio_invoice_id($this->getId());
        $obj->setbsio_order_id($orderId);
        $obj->setbsio_total($amount);
        $obj->save();
        $applied_total = $this->getBsioTotal();
        $this->setbsi_total_applied($applied_total);
        return $this;
    }

    public function removeOrder($orderId){
        $obj = $this->_invoiceOrderFactory->create();
        $obj->setId($orderId);
        $obj->delete();
        return $this;
    }

    public function getOrders(){
        $collection = $this->_invOrderCollectionFactory->create();
        $collection->addInvoiceFilterForPo($this->getId());
        return $collection;
    }


    public function getBsioTotal()
    {
        $total = 0;
        foreach($this->getAllOrderItems() as $item)
            $total += $item->getbsio_total();
        return $total;
    }


    public function addPayment($date, $method, $total, $notes){
        $obj = $this->_invoicePaymentsFactory->create();
        $obj->setbsip_invoice_id($this->getId());
        $obj->setbsip_date($date);
        $obj->setbsip_method($method);
        $obj->setbsip_total($total);
        $obj->setbsip_notes($notes);
        $obj->save();

        $total_paid = $this->getBsipTotal();
        $this->setbsi_total_paid($total_paid);
        $this->updateBsiStatus($total_paid);
        return $this;
    }

    public function removePayment($paymentId){
        $obj = $this->_invoicePaymentsFactory->create();
        $obj->setId($paymentId);
        $obj->delete();
        return $this;
    }

    public function getBsipTotal()
    {
        $total = 0;
        foreach($this->getAllPaymentsItems() as $item)
            $total += $item->getbsip_total();
        return $total;
    }


    public function updateBsiStatus($total_paid){
        $totalPaid = $total_paid;
        $invoiceTotal = $this->getbsi_total();
        
        if ($totalPaid >= $invoiceTotal){
            $status = 'paid';
        } elseif ($totalPaid >0 && $totalPaid < $invoiceTotal) {
            $status = 'partially_paid';
        } elseif ($totalPaid == 0) {
            $status = 'pending';
        } else {
            $status = 'pending';
        }

        $this->setbsi_status($status);
        return $status;
    }

    public function getCurrency()
    {
        return $this->getSupplier()->getCurrency();
    }

    public function getTotal()
    {
        return $this->getBsiTotal();
    }

    public function getBalanceDue()
    {
        return $this->getTotal() - $this->getTotalPaid();
    }

    public function getTotalToApply(){
        return $this->getBsiTotal() - $this->getBsiTotalApplied();
    }

    public function getTotalPaid()
    {
        return $this->getBsiTotalPaid();
    }

}
