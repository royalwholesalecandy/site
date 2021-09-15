<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class MissingStock extends AbstractDiscrepencies
{
    protected $_websiteCollection;
    protected $_missingStockResource;
    protected $_stockFactory;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\ResourceModel\Website\Collection $websiteCollection,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies\MissingStock $missingStockResource,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\CatalogInventory\StockFactory $stockFactory
    )
    {
        parent::__construct($stockRegistry);

        $this->_websiteCollection = $websiteCollection;
        $this->_missingStockResource = $missingStockResource;
        $this->_stockFactory = $stockFactory;
    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['missing_stock'] = ['explanations' => 'Missing Stock for website (table cataloginventory_stock)', 'items' => []];

        foreach($this->_websiteCollection as $website)
        {
            if (!$this->_missingStockResource->stockExistForWebsite($website->getId()))
            {
                $results['missing_stock']['items'][] = 'website_id='.$website->getId();

                if ($fix)
                {
                    $stock = $this->_stockFactory->create();
                    $stock->createStock($website->getId());
                    $stockId = $stock->getIdFromWebsiteId($website->getId());
                    $stock->createStockItemRecords($stockId, $website->getId());
                }
            }
        }

        return $results;
    }


}