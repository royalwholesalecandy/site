<?php

namespace BoostMyShop\Supplier\Model\Source;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Directory\Model\Resource\Country\Collection $countryCollection
     */
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

}
