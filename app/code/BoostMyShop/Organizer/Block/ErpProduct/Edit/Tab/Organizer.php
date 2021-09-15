<?php

namespace BoostMyShop\Organizer\Block\ErpProduct\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Organizer extends \BoostMyShop\Organizer\Block\OrganizerTab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

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

    public function getCurrentObject()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getCurrentObjectType()
    {
        return \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ERP_PRODUCT;
    }
    
    public function getGridHtml()
    {
        $block = $this->getLayout()
            ->createBlock('\BoostMyShop\Organizer\Block\Organizer\Grid')
            ->setOrganizerContext($this->getCurrentObjectType(), $this->getCurrentObject()->getId())
            ->setTemplate('Organizer/Grid.phtml')->toHtml();
        return $block;
    }

    public function getTabLabel()
    {
        return __('Organizer');
    }

    public function getTabTitle()
    {
        return __('Organizer');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }


}