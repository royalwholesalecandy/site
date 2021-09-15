<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Model\Config\Source;

class Warehouses
{
    protected $_warehouseCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
    )
    {
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function aroundToOptionArray(\BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $subject, $proceed)
    {
        $items = [];

        $collection = $this->_warehouseCollectionFactory->create()
                                        ->addActiveFilter()
                                        ->addFulfillementMethodFilter(\BoostMyShop\AdvancedStock\Model\Warehouse\FullfilmentMethod::METHOD_SHIPPING)
                                        ->setOrder('w_name', 'ASC');
        foreach($collection as $item)
        {
            $items[$item->getId()] = $item->getw_name();
        }

        return $items;
    }

}