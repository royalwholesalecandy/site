<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierOrganizerTab implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $supplier = $observer->getEvent()->getSupplier();
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();
        if($supplier->getId() > 0){
            $tabs->addTab(
                'organizer',
                [
                    'label' => __('Organizer'),
                    'title' => __('Organizer'),
                    'content' => $layout->createBlock('BoostMyShop\Organizer\Block\Supplier\Edit\Tab\Organizer')->toHtml()
                ]
            );
        }

        return $this;
    }


}
