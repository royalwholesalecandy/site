<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class CatalogInventoryStockItemAfterSave implements ObserverInterface
{
    protected $_logger;
    protected $_router;
    protected $_warehouseItemFactory;
    protected $_backendAuthSession;
    protected $_stockMovement;

    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovement $stockMovement,
        \BoostMyShop\AdvancedStock\Model\StockDiscrepencies\WrongStockItemQuantity $quantityFixer
    ) {
        $this->_logger = $logger;
        $this->_router = $router;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_stockMovement = $stockMovement;
        $this->_quantityFixer = $quantityFixer;
    }

    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        $stockItem = ($event->getItem()) ? $event->getItem() : $event->getDataObject();

        if ($stockItem)
        {
            if ($stockItem->getQty() != $stockItem->getOrigData('qty'))
            {
                $this->fix($stockItem);
            }
        }

        return $this;
    }

    public function fix($stockItem)
    {
        $expectedQty = $this->_router->getSellableQuantity($stockItem->getwebsite_id(), $stockItem->getproduct_id());
        $this->_logger->log('Checking stock sync for product #'.$stockItem->getproduct_id().' and website #'.$stockItem->getwebsite_id().' : '.$expectedQty.' VS '.$stockItem->getQty(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
        if ($expectedQty != $stockItem->getQty()) {
            $this->_logger->log('Quantity has been manually changed for #'.$stockItem->getproduct_id().' and website #'.$stockItem->getwebsite_id().' : apply system stock movement', \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
            $this->_quantityFixer->createStockMovementToSyncStockItemQuantity($stockItem, $expectedQty);
        }

        return $this;
    }

}
