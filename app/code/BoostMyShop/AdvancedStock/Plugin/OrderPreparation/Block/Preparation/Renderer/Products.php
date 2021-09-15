<?php

namespace BoostMyShop\AdvancedStock\Plugin\OrderPreparation\Block\Preparation\Renderer;

class Products
{
    protected $_extendedOrderItemCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $extendedOrderItemCollectionFactory
    )
    {
        $this->_extendedOrderItemCollectionFactory = $extendedOrderItemCollectionFactory;
    }

    public function aroundGetCollection(\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\Products $subject, $proceed, $order)
    {
        return $this->_extendedOrderItemCollectionFactory->create()->joinOrderItem()->addOrderFilter($order->getId())->addProductTypeFilter();
    }

    public function aroundRenderItem(\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\Products $subject, $proceed, $order, $item, $warehouseId)
    {
        $class = '';
        $suffix = '';
        $qty = $item->getesfoi_qty_to_ship();

        if ($warehouseId == $item->getesfoi_warehouse_id())
        {
            if ($item->getesfoi_qty_to_ship() == 0)
                $class = 'shipped';
            else
            {
                if ($item->getesfoi_qty_reserved() == 0)
                    $class = 'backorder';
                else
                {
                    if ($item->getesfoi_qty_reserved() < $item->getesfoi_qty_to_ship()) {
                        $class = 'partial';
                        $suffix = '('.$item->getesfoi_qty_reserved().'/'.$item->getesfoi_qty_to_ship().')';
                    }
                    else
                        $class = 'full';
                }
            }
        }
        else
        {
            $class = 'external';
        }

        return '<div class="preparation-item-'.$class.'">'.$qty.'x '.$item->getSku().' - '.$item->getName().' '.$suffix.'</div>';
    }

}