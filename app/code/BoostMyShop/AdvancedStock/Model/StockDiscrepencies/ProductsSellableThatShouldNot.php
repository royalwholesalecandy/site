<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class ProductsSellableThatShouldNot extends AbstractDiscrepencies
{
    protected $_stockItemCollectionFactory;
    protected $_config;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Stock\Item\CollectionFactory $stockItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config
    )
    {
        parent::__construct($stockRegistry);

        $this->_stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->_config = $config;
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['products_sellable_that_should_not'] = ['explanations' => 'Products without stock, no backorder, but sellable', 'items' => []];

        $defaultBackorderConfig = $this->_config->getMagentoBackorderSetting();

        $collection = $this->_stockItemCollectionFactory
                                    ->create()
                                    ->addFieldToFilter('is_in_stock', 1)
                                    ->addFieldToFilter('qty', 0)
                                    ->addNoBackorderFilter($defaultBackorderConfig)
                                    ->addSimpleProductFilter();
        foreach($collection as $item)
        {
            $results['products_sellable_that_should_not']['items'][] = 'Product #'.$item['product_id'].',  Stock #'.$item['stock_id'];
            if ($fix)
                $item->setis_in_stock(0)->save();
        }

        return $results;
    }

}
