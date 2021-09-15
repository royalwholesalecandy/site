<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model\Source;

class Warehouse
{
    protected $_warehouseCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
    ){
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function aroundToOptionArray(\BoostMyShop\Supplier\Model\Source\Warehouse $subject, $proceed)
    {
        $options = array();

        foreach($this->_warehouseCollectionFactory->create()->addActiveFilter() as $w)
            $options[] = array('value' => $w->getId(), 'label' => $w->getw_name());

        return $options;
    }
}