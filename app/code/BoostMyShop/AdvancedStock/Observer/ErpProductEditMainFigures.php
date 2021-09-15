<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpProductEditMainFigures implements ObserverInterface
{
    protected $_eventManager;
    protected $_router;
    protected $_warehouseItemFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_router = $router;
        $this->_warehouseItemFactory = $warehouseItemFactory;
    }

    public function execute(EventObserver $observer)
    {
        $block = $observer->getEvent()->getblock();
        $product = $observer->getEvent()->getProduct();

        $qtyOnHand = 0;
        $qtyToShip = 0;
        $qtyAvailable = 0;

        $warehouses =  $this->_router->getWarehousesForSales(1);
        foreach($warehouses as $w)
        {
            $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($product->getId(), $w);
            $qtyOnHand += $warehouseItem->getwi_physical_quantity();
            $qtyToShip += $warehouseItem->getwi_quantity_to_ship();
            $qtyAvailable += $warehouseItem->getwi_available_quantity();
        }


        $block->addFigure('Qty on hand', $qtyOnHand);
        $block->addFigure('Qty to ship', $qtyToShip);
        $block->addFigure('Qty available', $qtyAvailable);

        return $this;
    }

}
