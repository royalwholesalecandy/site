<?php
namespace BoostMyShop\Supplier\Block\Order\Edit;

class Summary extends \Magento\Backend\Block\Template
{
    protected $_template = 'Order/Edit/Summary.phtml';

    protected $_coreRegistry = null;


    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

}