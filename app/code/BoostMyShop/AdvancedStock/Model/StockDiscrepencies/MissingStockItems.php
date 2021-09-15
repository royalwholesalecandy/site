<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class MissingStockItems extends AbstractDiscrepencies
{
    protected $_resourceModel;
    protected $_stockHelper;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies\MissingStockItems $resourceModel,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\CatalogInventory\Stock $stockHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_resourceModel = $resourceModel;
        $this->_stockHelper = $stockHelper;

        parent::__construct($stockRegistry);

    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['missing_stock_items'] = ['explanations' => 'Missing Stock items (table cataloginventory_stock_item)', 'items' => []];

        $existingStockItems = $this->_resourceModel->getExisting();

        $required = $this->_resourceModel->getRequired();
        $missing = array_diff($required, $existingStockItems);
        foreach($missing as $item)
        {
            list($stockId, $websiteId, $productId) = explode('_', $item);
            $results['missing_stock_items']['items'][] = 'product_id='.$productId.', website_id='.$websiteId.' stock_id='.$stockId;

            if ($fix)
                $this->_stockHelper->createStockItemRecords($stockId, $websiteId, $productId);
        }

        return $results;
    }


}