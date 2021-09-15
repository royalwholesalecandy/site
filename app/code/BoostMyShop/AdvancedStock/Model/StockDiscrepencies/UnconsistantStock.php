<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class UnconsistantStock extends AbstractDiscrepencies
{
    protected $_websiteCollectionFactory;
    protected $_unconsistantStockResource;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies\UnconsistantStock $unconsistantStockResource
    )
    {
        parent::__construct($stockRegistry);

        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_unconsistantStockResource = $unconsistantStockResource;
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['unconsistant_stock'] = ['explanations' => 'Unconsistant Stock for website (table cataloginventory_stock) ', 'items' => []];

        $websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();

        $websiteCount = [];
        foreach($this->getStocks() as $stock)
        {
            if (!in_array($stock['website_id'], $websiteIds))
                $results['unconsistant_stock']['items'][] = 'Stock #'.$stock['stock_id'].' refers to unexisting website #'.$stock['website_id'].' (this error can not be fixed automatically, a DB intervention is required)';

            if (!isset($websiteCount[$stock['website_id']]))
                $websiteCount[$stock['website_id']] = 0;
            $websiteCount[$stock['website_id']] += 1;

            if ($websiteCount[$stock['website_id']] > 1)
                $results['unconsistant_stock']['items'][] = 'Several stocks refer to website #'.$stock['website_id'].' (this error can not be fixed automatically, a DB intervention is required)';
        }

        return $results;
    }

    protected function getStocks()
    {
        return $this->_unconsistantStockResource->getStocks();
    }


}
