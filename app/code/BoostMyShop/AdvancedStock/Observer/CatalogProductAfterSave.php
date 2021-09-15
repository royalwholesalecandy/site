<?php

namespace BoostMyShop\AdvancedStock\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class CatalogProductAfterSave implements ObserverInterface
{

    protected $_warehouseItemFactory;
    protected $_warehouseItemCollectionFactory;
    protected $_warehouseCollectionFactory;
    protected $_stockMovementFactory;
    protected $_backendAuthSession;
    protected $_logger;
    protected $_stockHelper;

    protected $_stockRegistry;
    protected $_stockItemRepository;


    /**
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\CatalogInventory\Stock $stockHelper,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_logger = $logger;
        $this->_stockHelper = $stockHelper;

        $this->_stockRegistry = $stockRegistry;
        $this->_stockItemRepository = $stockItemRepository;
    }

    /**
     * Saving product inventory data. Product qty calculated dynamically.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product)
            return;

        //if product just created
        if (!$product->getOrigData('entity_id')) {

            //product may already be linked to warehouse if product has been created with a quantity
            $existingWarehouseIds = $this->getExistingWarehouseIds($product->getId());
            foreach($this->getWarehouseIds() as $warehouseId) {
                if (!in_array($warehouseId, $existingWarehouseIds))
                    $this->_warehouseItemFactory->create()->createRecord($product->getId(), $warehouseId);
                else
                    $this->_logger->log('Do not initialize warehouse item for product#'.$product->getId().', and warehouse #'.$warehouseId.' because it already exists');
            }

            $defaultStockItem = $this->_stockRegistry->getStockItem($product->getId(), 0);

            foreach($this->_stockHelper->listStocks() as $stock)
            {
                $this->_logger->log('Create stock item for product#'.$product->getId().', stock #'.$stock['stock_id'].' website #'.$stock['website_id']);

                $stockItem = $this->_stockRegistry->getStockItem($product->getId(), $stock['website_id']);
                $stockItemData = $defaultStockItem->getData();
                unset($stockItemData['item_id']);
                $stockItemData['product_id'] = $product->getId();
                $stockItemData['stock_id'] = $stock['stock_id'];
                $stockItemData['website_id'] = $stock['website_id'];
                $stockItemData['qty'] = ($stockItemData['qty'] == null ? 0 : $stockItemData['qty']);

                $stockItem->addData($stockItemData);
                $this->_stockItemRepository->save($stockItem);

            }

        } else{
            if ($product->getOrigData('type_id') == 'simple' && $product->getData('type_id') == 'configurable') {
                foreach($this->_stockHelper->listStocks() as $stock)
                {
                    $stockItem = $this->_stockRegistry->getStockItem($product->getId(), $stock['website_id']);
                    $stockItemData = $stockItem->getData();

                    $stockItemData['is_in_stock'] = 1;

                    $stockItem->addData($stockItemData);
                    $this->_stockItemRepository->save($stockItem);
                }
            }
        }

        return $this;
    }

    protected function getWarehouseIds()
    {
        return $this->_warehouseCollectionFactory->create()->getAllIds();
    }

    protected function getExistingWarehouseIds($productId)
    {
        $ids = [];
        $collection = $this->_warehouseItemCollectionFactory->create()->addProductFilter($productId);
        foreach($collection as $item)
            $ids[] = $item->getwi_warehouse_id();

        return $ids;
    }

    protected function getUserId()
    {
        $userId = null;
        if ($this->_backendAuthSession->getUser())
            $userId = $this->_backendAuthSession->getUser()->getId();
        return $userId;
    }
}
