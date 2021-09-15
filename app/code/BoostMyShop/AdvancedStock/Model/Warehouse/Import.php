<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


//this class is only an alias for class ProductsImportHandler
class Import
{
    protected $_productImportHandler;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Warehouse\ProductsImportHandler $productImportHandler
    ) {
        $this->_productImportHandler = $productImportHandler;
    }

    public function process($warehouseId, $filePath)
    {

        return $this->_productImportHandler->importFromCsvFile($warehouseId, $filePath);
    }

    public function getResult()
    {
        return $this->_productImportHandler->getResult();
    }


}