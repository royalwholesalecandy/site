<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Supplier extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_template = 'ErpProduct/Edit/Tab/Supplier.phtml';

    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $coreRegistry;
    }

    public function getGridBlock()
    {
        $block = $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Supplier\Grid');
        return $block;
    }

    public function getFormBlock()
    {
        $block = $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Supplier\Form');
        return $block;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getTabLabel()
    {
        return __('Suppliers');
    }

    public function getTabTitle()
    {
        return __('Suppliers');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        $excludedProductTypes = ['configurable', 'bundle','grouped'];

        if (in_array($this->getProduct()->getTypeId(), $excludedProductTypes))
            return true;
        else
            return false;
    }


}