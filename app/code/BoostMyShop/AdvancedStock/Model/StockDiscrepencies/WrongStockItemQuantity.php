<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class WrongStockItemQuantity extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;
    protected $_router;
    protected $_warehouseItemFactory;
    protected $_backendAuthSession;
    protected $_stockMovement;
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovement $stockMovement,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->_router = $router;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_stockMovement = $stockMovement;
        $this->_logger = $logger;

        parent::__construct($stockRegistry);
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['wrong_stock_item_quantity'] = ['explanations' => 'Quantity in stock item', 'items' => []];

        $collection = $this->_stockItemCollectionFactory->create()->addSimpleProductFilter();
        foreach($collection as $item)
        {
            if (!$this->productManagesStock($item->getproduct_id()))
                continue;
            $expectedQty = $this->_router->getSellableQuantity($item->getwebsite_id(), $item->getproduct_id());
            if ((int)$expectedQty != (int)$item->getQty()) {
                $label = 'product_id=' . $item->getproduct_id() . ', website_id=' . $item->getwebsite_id() . ', stock_id='.$item->getstock_id() . ', item_id='.$item->getItemId() . ' (stored=' . $item->getQty() . ',expected=' . $expectedQty . ')';
                $results['wrong_stock_item_quantity']['items'][] = $label;
                if ($fix)
                {
                    $this->_logger->log('Fix wrong_stock_item_quantity : '.$label, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
                    //$this->createStockMovementToSyncStockItemQuantity($item, $expectedQty); <-- old code, doesnt really make sense, better to fix sellable qty
                    $this->_router->updateSalableQuantity($item->getwebsite_id(), $item->getproduct_id());
                }
            }
        }

        return $results;
    }

    public function createStockMovementToSyncStockItemQuantity($stockItem, $newQty)
    {
        $primaryWarehouse = $this->_router->getPrimaryWarehouse($stockItem->getwebsite_id());
        if (!$primaryWarehouse) {
            $this->_logger->log('Unable to update quantity, no primary warehouse configured for website #' . $stockItem->getwebsite_id());
            return $this;
        }

        $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($stockItem->getproduct_id(), $primaryWarehouse->getId());
        $diff = $stockItem->getQty() - $newQty;

        $userId = null;
        if ($this->_backendAuthSession->getUser())
            $userId = $this->_backendAuthSession->getUser()->getId();

        $targetQty = $warehouseItem->getwi_physical_quantity() + $diff;

        $this->_logger->log('Create stock movement to sync stock item quantity for product #'.$stockItem->getproduct_id().' and warehouse #'.$primaryWarehouse->getId().' to reach quantity '.$targetQty, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);

        $this->_stockMovement->updateProductQuantity($stockItem->getproduct_id(),
            $primaryWarehouse->getId(),
            $warehouseItem->getwi_physical_quantity(),
            $targetQty,
            'Quantity changed event',
            $userId);

        return $this;
    }

}