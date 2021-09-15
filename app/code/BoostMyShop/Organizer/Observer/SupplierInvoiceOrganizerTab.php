<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierInvoiceOrganizerTab implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();
        if($invoice->getId() > 0){
            $tabs->addTab(
                'organizer',
                [
                    'label' => __('Organizer'),
                    'title' => __('Organizer'),
                    'content' => $layout->createBlock('BoostMyShop\Organizer\Block\Supplier\Invoice\Edit\Tab\Organizer')->toHtml()
                ]
            );
        }

        return $this;
    }


}
