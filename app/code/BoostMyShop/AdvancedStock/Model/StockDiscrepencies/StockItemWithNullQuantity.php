<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class StockItemWithNullQuantity extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;

        parent::__construct($stockRegistry);

    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['stock_items'] = ['explanations' => 'Stock items with quantity NULL (not zero)', 'items' => []];

        $nullStockItems = $this->_stockItemCollectionFactory->create()->addFieldToFilter('qty', ['null' => true]);
        foreach($nullStockItems as $item)
        {
            $results['stock_items']['items'][] = 'product_id='.$item->getproduct_id().', stock_id='.$item->getStockId();
            if ($fix)
                $item->setQty(0)->save();
        }

        return $results;
    }
}