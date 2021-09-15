<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit\Tab;

class Po extends \Magento\Backend\Block\Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Invoice/Edit/Tab/Po.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_posFactory = null;
    protected $_invOrderCollectionFactory = null;
    protected $_invoiceFactory = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\CollectionFactory $posFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Order\CollectionFactory $invOrderCollectionFactory,
        \BoostMyShop\Supplier\Model\InvoiceFactory $invoiceFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_posFactory = $posFactory;
        $this->_invOrderCollectionFactory = $invOrderCollectionFactory;
        $this->_invoiceFactory = $invoiceFactory;
        parent::__construct($context, $data);
    }

    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_supplier_invoice');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/associateOrder', ['bsi_id' => $this->getInvoice()->getId()]);
    }

    public function getSupplierPo()
    {
        $collection = $this->_posFactory->create();
        $collection->addSupplierFilter($this->getInvoice()->getbsi_sup_id());
        $collection->setOrder('po_created_at', 'DESC');

        $linkedIds = $this->getAlreadyLinkedOrdersId();
        if(count($linkedIds) > 0){
            $collection->addFieldToFilter('po_id', array('nin' => $linkedIds));
        }
        
        return $collection;
    }

    public function getLinkOrders()
    {
        $orders = $this->getInvoice()->getOrders();
        return $orders;
    }

    protected function getAlreadyLinkedOrdersId()
    {
        $ids = $this->_invOrderCollectionFactory->create()->getAlreadyLinkedOrders($this->getInvoice()->getId());
        return $ids;
    }

}
