<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model;

class Product
{
    protected $_warehouseItemCollectionFactory;
    protected $_warehouseItemFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollection,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory
    ){
        $this->_warehouseItemCollectionFactory = $warehouseItemCollection;
        $this->_warehouseItemFactory = $warehouseItemFactory;
    }

    public function aroundGetLocation(\BoostMyShop\Supplier\Model\Product $subject, $proceed, $productId, $warehouseId)
    {
        if ($warehouseId)
        {
            $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
            return $warehouseItem->getwi_shelf_location();
        }
    }

    public function aroundGetStockDetails(\BoostMyShop\Supplier\Model\Product $subject, $proceed, $productId)
    {
        $html = [];

        if ($subject->productIsDeleted($productId))
            return 'Product deleted';

        $collection = $this->_warehouseItemCollectionFactory->create()->addProductFilter($productId)->joinWarehouse();
        foreach($collection as $item)
        {
            if (!$item->getwi_available_quantity() && !$item->getwi_physical_quantity() && !$item->getwi_quantity_to_ship())
                continue;

            $color = $this->getColor($item);
            $row = '<font color="'.$color.'">';
            $row .= $item->getw_name().' : '.$item->getwi_available_quantity().'/'.$item->getwi_physical_quantity();

            $details = [];
            if ($item->getwi_quantity_to_ship() > 0)
                $details[] = $item->getwi_quantity_to_ship().' to ship';
            if (count($details) > 0)
                $row .= ' <i>('.implode(', ', $details).')</i>';

            $row .= '</font>';
            $html[] = $row;
        }

        return implode('<br>', $html);
    }

    protected function getColor($item)
    {
        if ($item->getwi_available_quantity() <= 0)
            return 'red';

        if ($item->getQtyNeededForOrders() > 0)
            return 'red';


        return 'black';
    }
}