<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit\Tab;

class Payments extends \Magento\Backend\Block\Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Invoice/Edit/Tab/Payments.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $form = $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\PaymentForm');
        $grid = $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\Invoice\Edit\Tab\PaymentGrid');

        $this->setChild('payment_form', $form);
        $this->setChild('payment_grid', $grid);
    }

    protected function getInvoice()
    {
        return $this->_coreRegistry->registry('current_supplier_invoice');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addPayment', ['bsi_id' => $this->getInvoice()->getId()]);
    }

    public function getBalanceDueFormatted()
    {
        return $this->getInvoice()->getCurrency()->format($this->getInvoice()->getBalanceDue(), [], false);
    }

}
