<?php
namespace BoostMyShop\Organizer\Block\Supplier\Order\Edit\Tab;

class Organizer extends \BoostMyShop\Organizer\Block\OrganizerTab
{

    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
    }

    public function getCurrentObject()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

    public function getCurrentObjectType()
    {
        return \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_PURCHASE_ORDER;
    }

    public function getGridHtml()
    {
        $block = $this->getLayout()
            ->createBlock('\BoostMyShop\Organizer\Block\Organizer\Grid')
            ->setOrganizerContext($this->getCurrentObjectType(), $this->getCurrentObject()->getId())
            ->setTemplate('Organizer/Grid.phtml')->toHtml();
        return $block;
    }

}