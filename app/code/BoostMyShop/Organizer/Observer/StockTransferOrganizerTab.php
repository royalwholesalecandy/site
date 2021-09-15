<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class StockTransferOrganizerTab implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $transfer = $observer->getEvent()->getTransfer();
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();
        if($transfer->getId() > 0){
            $tabs->addTab(
                'organizer',
                [
                    'label' => __('Organizer'),
                    'title' => __('Organizer'),
                    'content' => $layout->createBlock('BoostMyShop\Organizer\Block\Transfer\Edit\Tab\Organizer')->toHtml()
                ]
            );
        }

        return $this;
    }


}
