<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierReplenishmentGridPrepareColumns implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter('sales_history', ['header' => __('Sales History'), 'filter' => false, 'align' => 'center', 'sortable' => false, 'index' => 'sales_history'], 'stock_details');
        $grid->addColumnAfter('avg_sales', ['header' => __('Avg sales per week'), 'type' => 'number', 'index' => 'avg_sales'], 'sales_history');
        $grid->addColumnAfter('run_out', ['header' => __('Run out (days)'), 'type' => 'number', 'index' => 'run_out'], 'avg_sales');

        return $this;
    }
}
