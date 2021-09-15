<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierOrderAddProductsGridPrepareColumns implements ObserverInterface
{

    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter('sales_history', ['header' => __('Sales History'), 'filter' => false, 'align' => 'center', 'sortable' => false, 'index' => 'sales_history', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer\StockHistory'], 'stock_details');

        return $this;
    }
}
