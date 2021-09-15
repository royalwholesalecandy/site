<?php
namespace BoostMyShop\Organizer\Block\Organizer;

class RelatedOrganizer extends \Magento\Backend\Block\Template
{
    protected $_template = 'RelatedOrganizer.phtml';
    protected $_organizerCollectionFactory;
    private $_objType = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \BoostMyShop\Organizer\Model\ResourceModel\Organizer\CollectionFactory $organizerCollectionFactory,
        array $data = []
    )
    {
    	$this->_organizerCollectionFactory = $organizerCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getRelatedOrganizers()
    {
    	return $this->_organizerCollectionFactory->create()->addFieldToFilter('o_object_type', $this->_objType);
    }

    public function setObjectType($objType)
    {
    	$this->_objType = $objType;
    	return $this;
    }

}