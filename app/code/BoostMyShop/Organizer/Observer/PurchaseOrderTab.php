<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class PurchaseOrderTab implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $tabs = $observer->getEvent()->getTabs();
        $layout = $observer->getEvent()->getLayout();
        if($order->getId() > 0){
            $tabs->addTab(
                'organizer',
                [
                    'label' => __('Organizer'),
                    'title' => __('Organizer'),
                    'content' => $layout->createBlock('BoostMyShop\Organizer\Block\Supplier\Order\Edit\Tab\Organizer')->toHtml()
                ]
            );
        }

        return $this;
    }


}
