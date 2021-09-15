<?php

namespace BoostMyShop\Supplier\Model\Source;

class Supplier implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Directory\Model\Resource\Country\Collection $countryCollection
     */
    public function __construct(\BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();
        $collection = $this->_collectionFactory->create();

        foreach($collection as $item)
        {
            $options[] = array('value' => $item->getId(), 'label' => $item->getSupName());
        }

        return $options;
    }

    public function toArray()
    {
        $options = array();
        $collection = $this->_collectionFactory->create();

        foreach($collection as $item)
        {
            $options[$item->getId()] = $item->getSupName();
        }

        return $options;

    }
}
