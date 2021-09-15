<?php
namespace BoostMyShop\Organizer\Block\Preparation\Packing;

class Organizer extends \Magento\Backend\Block\Template
{
    protected $_organizerCollectionFactory;
    protected $_coreRegistry = null;

    protected $_template = 'Organizer/Preparation/Packing/Organizer.phtml';

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \BoostMyShop\Organizer\Model\ResourceModel\Organizer\CollectionFactory $organizerCollectionFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_organizerCollectionFactory = $organizerCollectionFactory;
        $this->_coreRegistry = $registry;
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_packing_order')->getOrder();
    }

    public function getOrganizers()
    {
        if ($this->getOrder()->getId())
            return $this->_organizerCollectionFactory->create()->addObjectFilter(\BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER, $this->getOrder()->getId());
        else
            return false;
    }

    public function showSection()
    {
        $organizers = $this->getOrganizers();
        if (!$organizers)
            return false;
        return ($this->getOrganizers()->getSize() > 0);
    }

}