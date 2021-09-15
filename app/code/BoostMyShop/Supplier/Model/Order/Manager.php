<?php

namespace BoostMyShop\Supplier\Model\Order;

class Manager implements \Magento\Framework\Option\ArrayInterface
{
    protected $_userCollectionFactory;

    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
    )
    {
        $this->_userCollectionFactory = $userCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();

        foreach($this->_userCollectionFactory->create() as $item)
        {
            $options[$item->getId()] = $item->getusername();
        }

        return $options;
    }

}
