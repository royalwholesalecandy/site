<?php
namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab;

class All extends \Magento\Backend\Block\Template
{
    protected $_template = 'Product/Edit/Tab/All.phtml';

    protected $_coreRegistry;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $coreRegistry,
                                array $data = [])
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }


    public function getProduct()
    {
        if ($this->_coreRegistry->registry('advancedstock_current_product'))
            return $this->_coreRegistry->registry('advancedstock_current_product');
        else
            return $this->_coreRegistry->registry('current_product');
    }

    public function getProductId()
    {
        if ($this->getProduct())
            return $this->getProduct()->getId();
        else
            return false;
    }

}