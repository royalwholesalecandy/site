<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Model;

class Product
{
    protected $warehouseItemFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory
    )
    {
        $this->_warehouseItemFactory= $warehouseItemFactory;
    }

    public function aroundGetLocation(\BoostMyShop\OrderPreparation\Model\Product $subject, $proceed, $productId, $warehouseId)
    {
        $item = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
        if ($item)
            return $item->getwi_shelf_location();
    }

}