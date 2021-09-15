<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


class ProductsImportHandler
{

    protected $csvProcessor;
    protected $_product;
    protected $fieldsIndexes = [];
    protected $_warehouseId;
    protected $_warehouseItemFactory;
    protected $_stockMovementFactory;

    protected $_results = [];

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\Product $product,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_product = $product;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
    }

    public function importFromCsvFile($warehouseId, $filePath)
    {
        $this->_warehouseId = $warehouseId;

        if (!($filePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        //perform checks
        $this->csvProcessor->setDelimiter(';');
        $rows = $this->csvProcessor->getData($filePath);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        $count = 0;
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            if ($this->_importRow($rowData))
                $count++;
        }

        return $count;
    }



    protected function _importRow($rowData)
    {
        $sku = $rowData[$this->fieldsIndexes['sku']];
        $qty = (isset($this->fieldsIndexes['qty']) ? $rowData[$this->fieldsIndexes['qty']] : '');
        $shelfLocation = (isset($this->fieldsIndexes['shelf_location']) ? $rowData[$this->fieldsIndexes['shelf_location']] : '');
        $warningStockLevel = (isset($this->fieldsIndexes['warning_stock_level']) ? $rowData[$this->fieldsIndexes['warning_stock_level']] : '');
        $useConfigWarningStockLevel = (isset($this->fieldsIndexes['use_config_warning_stock_level']) ? $rowData[$this->fieldsIndexes['use_config_warning_stock_level']] : '');
        $idealStockLevel = (isset($this->fieldsIndexes['ideal_stock_level']) ? $rowData[$this->fieldsIndexes['ideal_stock_level']] : '');
        $useConfigIdealStockLevel = (isset($this->fieldsIndexes['use_config_ideal_stock_level']) ? $rowData[$this->fieldsIndexes['use_config_ideal_stock_level']] : '');

        //check sku
        $productId = $this->_product->getIdBySku($sku);
        if (!$productId) {
            $this->_results[] = 'Unknown sku : '.$sku;
            return false;
        }

        $stockItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $this->_warehouseId);

        if ($shelfLocation != '')
            $stockItem->setwi_shelf_location($shelfLocation);

        if ($warningStockLevel != '')
            $stockItem->setwi_warning_stock_level($warningStockLevel);

        if ($useConfigWarningStockLevel != '')
            $stockItem->setwi_use_config_warning_stock_level($useConfigWarningStockLevel);

        if ($idealStockLevel != '')
            $stockItem->setwi_ideal_stock_level($idealStockLevel);

        if ($useConfigIdealStockLevel != '')
            $stockItem->setwi_use_config_ideal_stock_level($useConfigIdealStockLevel);

        $stockItem->save();

        //manage quantity via stock movement
        if (($qty != '') && ($qty >= 0))
        {
            $userId = null;

            $this->_stockMovementFactory->create()->updateProductQuantity($stockItem->getwi_product_id(),
                $stockItem->getwi_warehouse_id(),
                $stockItem->getwi_physical_quantity(),
                $qty,
                'From warehouse import',
                $userId);

            $this->_results[] = 'Stock updated for sku '.$sku.' to '.$qty;
        }

        return true;
    }

    public function checkColumns($columns)
    {
        $mandatory = [
                        0 => 'sku'
                    ];
        for($i=0;$i<count($columns);$i++)
        {
            $this->fieldsIndexes[$columns[$i]] = $i;
        }

        foreach($mandatory as $field)
        {
            if (!isset($this->fieldsIndexes[$field]))
                throw new \Exception('Mandatory column '.$field.' is missing');
        }

        return true;
    }

    public function getResult()
    {
        return $this->_results;
    }

}
