<?php

namespace BoostMyShop\AdvancedStock\Model\Config\Source;

class Warehouse implements \Magento\Framework\Option\ArrayInterface
{

    protected $_collectionFactory;

    public function __construct(\BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();
        $collection = $this->_collectionFactory->create();

        foreach($collection as $item)
        {
            $options[] = array('value' => $item->getId(), 'label' => $item->getw_name());
        }

        return $options;
    }

    public function toArray()
    {

    }

}
