<?php
namespace BoostMyShop\AdvancedStock\Block\MassStockEditor;

class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'MassStockEditor/Header.phtml';

    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}