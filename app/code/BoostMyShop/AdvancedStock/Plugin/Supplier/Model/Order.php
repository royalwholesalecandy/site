<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model;

class Order
{

    protected $_warehouseFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
    ) {
        $this->_warehouseFactory = $warehouseFactory;
    }

    public function aroundGetShippingAddress(\BoostMyShop\Supplier\Model\Order $subject, $proceed)
    {
        $warehouse = $this->_warehouseFactory->create()->load($subject->getpo_warehouse_id());
        return $warehouse->getAddress();
    }


}
