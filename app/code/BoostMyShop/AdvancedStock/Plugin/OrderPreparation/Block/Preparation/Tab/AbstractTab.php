<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Tab;

class AbstractTab
{
    protected $_extendedOrderItemCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedOrderItemCollectionFactory
    )
    {
        $this->_extendedOrderItemCollectionFactory = $extendedOrderItemCollectionFactory;
    }

    protected function getOpenedOrderIdForWarehouse($warehouseId)
    {
        return $this->_extendedOrderItemCollectionFactory->create()->addQtyToShipFilter()->joinOrderItem()->joinOpenedOrder()->addWarehouseFilter($warehouseId)->getOrderIds();
    }

}
