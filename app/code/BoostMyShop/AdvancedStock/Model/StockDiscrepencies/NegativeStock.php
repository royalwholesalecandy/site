<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class NegativeStock extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;
    protected $_warehouseItemCollectionFactory;
    protected $_stockMovementFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->_stockMovementFactory = $stockMovementFactory;

        parent::__construct($stockRegistry);

    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['warehouse_item'] = ['explanations' => 'Warehouse items with negative physical quantity', 'items' => []];
        $results['cataloginventory_stock_item'] = ['explanations' => 'Stock items with negative physical quantity', 'items' => []];

        $negativeStockItems = $this->_stockItemCollectionFactory->create()->addFieldToFilter('qty', ['lt' => 0]);
        foreach($negativeStockItems as $item)
        {
            if (!$this->productManagesStock($item->getproduct_id()))
                continue;
            $results['cataloginventory_stock_item']['items'][] = 'product_id='.$item->getproduct_id().', stock_id='.$item->getStockId();
            if ($fix)
                $item->setQty(0)->save();
        }

        $negativeWarehouseItems = $this->_warehouseItemCollectionFactory->create()->addFieldToFilter('wi_physical_quantity', ['lt' => 0]);
        foreach($negativeWarehouseItems as $item)
        {
            if (!$this->productManagesStock($item->getwi_product_id()))
                continue;
            $results['warehouse_item']['items'][] = 'product_id='.$item->getwi_product_id().', warehouse_id='.$item->getwi_warehouse_id();
            if ($fix)
                $this->_stockMovementFactory->create()->updateProductQuantity($item->getwi_product_id(), $item->getwi_warehouse_id(), $item->getwi_physical_quantity(), 0, 'Negative qty fix', 0);
        }

        return $results;
    }
}