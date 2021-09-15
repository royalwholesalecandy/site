<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class ProductsNotSellable extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory
    )
    {
        parent::__construct($stockRegistry);

        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['products_not_sellable'] = ['explanations' => 'Products with a quantity sellable but out of stock', 'items' => []];

        $collection = $this->_stockItemCollectionFactory
                                    ->create()
                                    ->addFieldToFilter('is_in_stock', 0)
                                    ->addFieldToFilter('qty', ['gt' => 0])
                                    ->addSimpleProductFilter();
        foreach($collection as $item)
        {
            $results['products_not_sellable']['items'][] = 'Product #'.$item['product_id'].',  Stock #'.$item['stock_id'].' : qty sellable '.(int)$item['qty'];
            if ($fix)
                $item->setis_in_stock(1)->save();
        }

        return $results;
    }

}
