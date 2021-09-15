<?php
namespace BoostMyShop\Supplier\Block\Order;

class NewOrder extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/New.phtml';

    protected $_supplierCollectionFactory;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollectionFactory, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_supplierCollectionFactory = $supplierCollectionFactory;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/Edit');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/Index');
    }

    public function getSuppliers()
    {
        return $this->_supplierCollectionFactory->create()->setOrder('sup_name', 'ASC');
    }

    public function getNewSupplierUrl()
    {
        return $this->getUrl('supplier/supplier/Edit');
    }

}