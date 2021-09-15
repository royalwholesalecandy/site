<?php

namespace BoostMyShop\Supplier\Model\Source;

class Website implements \Magento\Framework\Option\ArrayInterface
{
    protected $_websiteCollectionFactory;

    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
    )
    {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();

        foreach($this->_websiteCollectionFactory->create() as $website)
            $options[$website->getId()] = $website->getName();

        return $options;
    }

}
