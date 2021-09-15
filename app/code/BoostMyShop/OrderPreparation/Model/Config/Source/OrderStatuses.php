<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class OrderStatuses implements \Magento\Framework\Option\ArrayInterface
{
    protected $_collectionFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $collectionFactory
    )
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $statuses = [];

        foreach($this->_collectionFactory->create() as $item)
        {
            $statuses[] = array('value' => $item->getStatus(), 'label' => $item->getLabel());
        }

        return $statuses;
    }

}
