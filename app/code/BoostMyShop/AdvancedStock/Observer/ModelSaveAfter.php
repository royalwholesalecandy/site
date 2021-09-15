<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ModelSaveAfter implements ObserverInterface
{
    protected $_eventManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_eventManager = $eventManager;
    }

    public function execute(EventObserver $observer)
    {
        $object = $observer->getEvent()->getObject();
        if ($object)
        {
            switch(get_class($object))
            {
                case 'Magento\CatalogInventory\Model\Adminhtml\Stock\Item':
                case 'Magento\CatalogInventory\Model\Stock\Item':
                    $this->_eventManager->dispatch('cataloginventory_stock_item_save_after', ['item' => $object]);
                    break;
            }
        }

        return $this;
    }


}
