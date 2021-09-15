<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


class Export
{
    protected $_productCollectionFactory;
    protected $_warehouseItemFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Product\AllFactory $productCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\ItemFactory $warehouseItemFactory
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
    }

    public function getProducts($warehouseId, $date = null)
    {
        $products =  [];

        foreach($this->getCollection($warehouseId) as $item)
        {
            $products[] = $this->getProductDetails($item, $date);
        }

        return $products;
    }

    protected function getCollection($warehouseId)
    {
        return $this->_productCollectionFactory->create()->addWarehouseFilter($warehouseId);
    }

    protected function getProductDetails($item, $date)
    {
        $details = array();

        $details['product_id'] = $item['wi_product_id'];
        $details['warehouse_id'] = $item['wi_warehouse_id'];
        $details['sku'] = $item['sku'];
        $details['product'] = $item['name'];
        $details['cost'] = $item['cost'];
        $details['shelf_location'] = $item['wi_shelf_location'];

        if (!$date) {
            $details['qty'] = $item['wi_physical_quantity'];
            $details['qty_to_ship'] = $item['wi_quantity_to_ship'];
            $details['qty_available'] = $item['wi_available_quantity'];
        }
        else {

            $date .= ' 23:59:59';

            $details['qty'] = $this->_warehouseItemFactory->create()->calculatePhysicalQuantityFromStockMovements($item['wi_warehouse_id'], $item['wi_product_id'], $date);
        }

        return $details;
    }

    public function convertToCsv($products, $filePath)
    {
        $isHeader = true;

        $content = "";
        $separator = ";";
        $newLine = "\n";

        foreach($products as $product)
        {
            if ($isHeader)
            {
                $content .= implode($separator, array_keys($product)).$newLine;
                $isHeader = false;
            }

            $content .= implode($separator, $product).$newLine;
        }

        file_put_contents($filePath, $content);
    }

}