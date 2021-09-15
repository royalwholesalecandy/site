<?php
namespace BoostMyShop\Supplier\Block\Invoice\Edit;

class Summary extends \Magento\Backend\Block\Template
{
    protected $_template = 'Invoice/Edit/Summary.phtml';

    protected $_coreRegistry = null;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
        )
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_supplier_invoice');
    }

}