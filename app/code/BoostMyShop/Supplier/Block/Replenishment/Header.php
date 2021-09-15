<?php
namespace BoostMyShop\Supplier\Block\Replenishment;

class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'Replenishment/Header.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_supplierCollectionFactory = $supplierCollectionFactory;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/CreateOrder');
    }

    public function getSuppliers()
    {
        return $this->_supplierCollectionFactory->create()->setOrder('sup_name', 'ASC');
    }

}