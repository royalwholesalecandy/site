<?php

namespace BoostMyShop\AdvancedStock\Model\Config\Source;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{

    protected $_collectionFactory;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();
        $collection = $this->_collectionFactory->create()->addVisibleFilter();

        $options[] = array('value' => '', 'label' => __('--Please Select--'));
        foreach($collection as $item)
        {
            $options[] = array('value' => $item->getAttributeCode(), 'label' => $item->getAttributeCode());
        }

        return $options;
    }

    public function toArray()
    {

    }

}
