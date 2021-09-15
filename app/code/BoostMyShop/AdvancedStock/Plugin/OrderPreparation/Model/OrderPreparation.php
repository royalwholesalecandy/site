<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Model;

class OrderPreparation
{
    protected $_extendedOrderItemCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedOrderItemCollectionFactory
    )
    {
        $this->_extendedOrderItemCollectionFactory = $extendedOrderItemCollectionFactory;
    }

    public function aroundGetItemsToShip(\BoostMyShop\OrderPreparation\Model\OrderPreparation $subject, $proceed, $order, $warehouseId)
    {
        $items = [];

        $collection = $this->_extendedOrderItemCollectionFactory
                            ->create()
                            ->joinOrderItem()
                            ->addOrderFilter($order->getId())
                            ->addQtyToShipFilter()
                            ->addProductTypeFilter()
                            ->addWarehouseFilter($warehouseId)
                            ;
        foreach($collection as $item)
        {
            if ($item->getesfoi_qty_reserved() > 0)
                $items[$item->getesfoi_order_item_id()] = $item->getesfoi_qty_reserved();
        }

        return $items;
    }

}