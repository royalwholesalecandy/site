<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierTransitGridPrepareColumns implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter('backorder_qty', ['header' => __('Backorder qty'), 'type' => 'number', 'align' => 'right', 'index' => 'backorder_qty'], 'qty_to_receive');

        return $this;
    }
}
