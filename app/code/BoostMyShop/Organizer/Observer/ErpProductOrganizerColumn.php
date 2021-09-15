<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpProductOrganizerColumn implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getGrid();

        $grid->addColumnAfter(
            'organizer', 
            [
                'align' => 'center', 
                'header' => __('Organizer'), 
                'index' => 'entity_id', 
                'entity' => 'product',
                'filter' => false, 
                'sortable' => false, 
                'type' => 'renderer', 
                'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Organizer'
            ],
            'id'
        );

        return $this;
    }


}
