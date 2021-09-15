<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class OrderItemWarehouseReservationToProcess implements ObserverInterface
{

    protected $_reservationFixer;
    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Warehouse\Item\ReservationFixer $reservationFixer
    ) {
        $this->_reservationFixer = $reservationFixer;
    }

    public function execute(EventObserver $observer)
    {
        $warehouseItem = $observer->getEvent()->getWarehouseItem();

        $this->_reservationFixer->fixForWarehouseItem($warehouseItem);

        return $this;
    }

}
