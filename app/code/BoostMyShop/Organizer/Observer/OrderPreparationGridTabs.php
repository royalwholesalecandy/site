<?php

namespace BoostMyShop\Organizer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OrderPreparationGridTabs implements ObserverInterface
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
                'entity' => \BoostMyShop\Organizer\Model\Organizer::OBJECT_TYPE_ORDER,
                'filter' => false,
                'sortable' => false,
                'type' => 'renderer',
                'renderer' => '\BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer\Organizer'
            ],
            'increment_id'
        );

        return $this;
    }


}
