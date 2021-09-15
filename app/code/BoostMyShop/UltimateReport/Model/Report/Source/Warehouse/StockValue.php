<?php

namespace BoostMyShop\UltimateReport\Model\Report\Source\Warehouse;

class StockValue extends \BoostMyShop\UltimateReport\Model\Report\Source\AbstractSource
{
    protected $_urRegistry;
    protected $_reportResource;

    public function __construct(
        \BoostMyShop\UltimateReport\Model\ResourceModel\Report $reportResource,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\UltimateReport\Model\Registry $urRegistry
    ) {
        $this->_urRegistry = $urRegistry;
        $this->_reportResource = $reportResource;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function getSeries()
    {
        $series = [];
        $series[] = ['label' => "Stock value", 'column' => 'y'];
        return $series;
    }

    public function getReportDatas($max = null)
    {
        $data = [];

        $warehouses = $this->_warehouseCollectionFactory->create();
        foreach($warehouses as $warehouse)
        {
            $data[] = ['x' => $warehouse->getw_name(), 'y' => $this->getStockValue($warehouse->getId())];
        }

        return $data;
    }

    public function getStockValue($warehouseId)
    {
        $costAttributeId = 81;

        $sql = $this->_reportResource->getDbConnection()->select()
                ->from(array('tbl_warehouse_item' => $this->_reportResource->getTableName('bms_advancedstock_warehouse_item')),
                array('value' => 'sum(wi_physical_quantity * value)'))
                ->join($this->_reportResource->getTableName('catalog_product_entity_decimal'), 'entity_id = wi_product_id')
                ->where('store_id = 0')
                ->where('wi_warehouse_id = '.$warehouseId)
                ->where('attribute_id = ' . $costAttributeId);

        $value = $this->_reportResource->getDbConnection()->fetchOne($sql);
        if ($value < 0)
            $value = 0;
        return $value;
    }

}