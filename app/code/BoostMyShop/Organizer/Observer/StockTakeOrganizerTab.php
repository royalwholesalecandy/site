<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class StockTakeOrganizerTab implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $stocktake = $observer->getEvent()->getStockTake();
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();
        if($stocktake->getId() > 0){
            $tabs->addTab(
                'organizer',
                [
                    'label' => __('Organizer'),
                    'title' => __('Organizer'),
                    'content' => $layout->createBlock('BoostMyShop\Organizer\Block\StockTake\Edit\Tab\Organizer')->toHtml()
                ]
            );
        }

        return $this;
    }


}
