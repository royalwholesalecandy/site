<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SupplierInvoiceOrganizerColumn implements ObserverInterface
{
    
    public function execute(EventObserver $observer)
    {
        $grid = $observer->getEvent()->getGrid();

        $grid->addColumnAfter(
            'organizer', 
            [
                'align' => 'center', 
                'header' => __('Organizer'), 
                'index' => 'bsi_id', 
                'entity' => 'supplier_invoice',
                'filter' => false, 
                'sortable' => false, 
                'type' => 'renderer', 
                'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Organizer'
            ],
            'bsi_id'
        );

        return $this;
    }


}
