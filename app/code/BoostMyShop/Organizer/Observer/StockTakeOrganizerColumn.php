<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class StockTakeOrganizerColumn implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getGrid();

        $grid->addColumnAfter(
            'organizer', 
            [
                'align' => 'center', 
                'header' => __('Organizer'), 
                'index' => 'sta_id', 
                'entity' => 'stock_take',
                'filter' => false, 
                'sortable' => false, 
                'type' => 'renderer', 
                'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Organizer'
            ],
            'sta_name'
        );

        return $this;
    }


}
