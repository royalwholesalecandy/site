<?php
namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab;

class Import extends \Magento\Backend\Block\Template
{
    protected $_template = 'Warehouse/Edit/Tab/Import.phtml';

    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

}