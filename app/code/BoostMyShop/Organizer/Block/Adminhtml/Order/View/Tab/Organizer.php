<?php

namespace BoostMyShop\Organizer\Block\Adminhtml\Order\View\Tab;


class Organizer extends \BoostMyShop\Organizer\Block\OrganizerTab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

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
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getCurrentObject()
    {
        return $this->coreRegistry->registry('current_order');
    }
    
    public function getCurrentObjectType()
    {
        return \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER;
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        $block = $this->getLayout()
            ->createBlock('\BoostMyShop\Organizer\Block\Organizer\Grid')
            ->setOrganizerContext($this->getCurrentObjectType(), $this->getCurrentObject()->getId())
            ->setTemplate('Organizer/Grid.phtml')->toHtml();
        return $block;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Organizer');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Organizer');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }
    
}