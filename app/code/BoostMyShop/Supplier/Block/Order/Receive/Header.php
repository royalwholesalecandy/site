<?php
namespace BoostMyShop\Supplier\Block\Order\Receive;

class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/Receive/Header.phtml';

    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getBackUrl()
    {

        return $this->getUrl('*/*/edit', ['po_id' => $this->_coreRegistry->registry('current_purchase_order')->getId()]);
    }
}