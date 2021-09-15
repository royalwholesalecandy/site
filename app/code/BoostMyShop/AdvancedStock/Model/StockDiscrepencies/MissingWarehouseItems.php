<?php

namespace BoostMyShop\AdvancedStock\Model\StockDiscrepencies;

class MissingWarehouseItems extends AbstractDiscrepencies
{
    protected $_resourceModel;
    protected $_warehouseItemFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockDiscrepencies\MissingWarehouseItems $resourceModel,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_resourceModel = $resourceModel;
        $this->_warehouseItemFactory = $warehouseItemFactory;

        parent::__construct($stockRegistry);

    }

    public function run(&$results, $fix, $productId = null)
    {
        $results['missing_warehouse_items'] = ['explanations' => 'Missing Warehouse items', 'items' => []];

        $existingWarehouseItems = $this->_resourceModel->getExisting();
        $required = $this->_resourceModel->getRequired();
        $missing = array_diff($required, $existingWarehouseItems);
        foreach($missing as $item)
        {
            list($warehouseId, $productId) = explode('_', $item);
            $results['missing_warehouse_items']['items'][] = 'product_id='.$productId.', warehouse_id='.$warehouseId;
            if ($fix)
                $this->insertMissing($warehouseId, $productId);
        }

        return $results;
    }

    protected function insertMissing($warehouseId, $productId)
    {
        return $this->_warehouseItemFactory->create()->createRecord($productId, $warehouseId);
    }

}