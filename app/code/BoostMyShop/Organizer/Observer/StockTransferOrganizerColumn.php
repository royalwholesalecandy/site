<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class StockTransferOrganizerColumn implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getGrid();

        $grid->addColumnAfter(
            'organizer', 
            [
                'align' => 'center', 
                'header' => __('Organizer'), 
                'index' => 'st_id', 
                'entity' => 'stock_transfer',
                'filter' => false, 
                'sortable' => false, 
                'type' => 'renderer', 
                'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Organizer'
            ],
            'st_created_at'
        );

        return $this;
    }


}
