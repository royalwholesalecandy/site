<?php

namespace BoostMyShop\UltimateReport\Model\Report\Source\Product;

class StockLevelHistory extends \BoostMyShop\UltimateReport\Model\Report\Source\AbstractSource
{
    protected $_stockMovementCollectionFactory;
    protected $_urRegistry;
    protected $_reportResource;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovement\CollectionFactory $stockMovementCollectionFactory,
        \BoostMyShop\UltimateReport\Model\ResourceModel\Report $reportResource,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\UltimateReport\Model\Registry $urRegistry
    ) {
        $this->_stockMovementCollectionFactory = $stockMovementCollectionFactory;
        $this->_urRegistry = $urRegistry;
        $this->_reportResource = $reportResource;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    public function getSeries()
    {
        $series = [];

        foreach($this->_warehouseCollectionFactory->create() as $warehouse)
        {
            $serie = ['label' => $warehouse->getw_name(), 'column' => 'stock_'.$warehouse->getId()];
            $series[] = $serie;
        }

        return $series;
    }

    public function getReportDatas($max = null)
    {
        $data = [];

        $filters = $this->_urRegistry->getFilters();

        $productId = $filters['product_id'];
        $warehouses = $this->_warehouseCollectionFactory->create();

        $alldates = $this->getAllDates($filters);
        foreach($alldates as $date)
        {
            $row = ['x' => $date['label']];
            foreach($warehouses as $warehouse)
            {
                $stockLevel = $this->getStockLevel($productId, $warehouse->getId(), $date['date']);
                $row['stock_'.$warehouse->getId()] = $stockLevel;
            }

            $data[] = $row;
        }

        return $data;
    }

    public function getAllDates($filters)
    {
        $dates = [];

        $interval = null;
        $format = null;
        $startDate = null;
        switch($filters['group_by_date'])
        {
            case '%d %b %y %h %p':  //hours
                $modifyExpr = "+1 hour";
                $format = "M-d H:i";
                $startDate = $filters['date_from'].' 00:59:59';
                $endDate = $filters['date_to'].' 23:59:59';
                break;
            case '%d %b %y':    //day
                $modifyExpr = "+1 day";
                $format = "Y-M-d";
                $startDate = date('Y-M-d', strtotime($filters['date_from'])).' 23:59:59';
                $endDate = date('Y-M-d', strtotime($filters['date_to'])).' 23:59:59';
                break;
            case '%v %Y':   //week
                $modifyExpr = "+1 week";
                $format = "Y W";
                $tmpDate = new \DateTime($filters['date_from']);
                $tmpDate->modify('next sunday');
                $startDate = $tmpDate->format('Y-m-d').' 23:59:59';
                $tmpDate = new \DateTime($filters['date_to']);
                $tmpDate->modify('next sunday');
                $endDate = $tmpDate->format('Y-m-d').' 23:59:59';
                break;
            case '%b %Y':    //month
                $modifyExpr = "last day of next month";
                $format = "Y-M";
                $startDate = date('Y-M-t', strtotime($filters['date_from'])).' 23:59:59';
                $endDate = date('Y-M-t', strtotime($filters['date_to'])).' 23:59:59';
                break;
            case '%Y':      //year
                $modifyExpr = "last day of next year";
                $format = "Y";
                $startDate = date('Y', strtotime($filters['date_from'])).'-12-31 23:59:59';
                $endDate = date('Y', strtotime($filters['date_to'])).'-12-31 23:59:59';
                break;
        }
        $dateObj = new \DateTime($startDate);
        $endDateObj = new \DateTime($endDate);
        while ($dateObj <= $endDateObj)
        {
            $dates[] = ['label' => $dateObj->format($format), 'date' => $dateObj->getTimestamp()];
            $dateObj->modify($modifyExpr);

        }

        return $dates;
    }

    public function getStockLevel($productId, $warehouseId, $maxTimeStamp)
    {

        $maxDate = date('Y-m-d H:i:s', $maxTimeStamp);

        $sql = $this->_reportResource->getDbConnection()->select()
            ->from(array('tbl_stock_movement' => $this->_reportResource->getTableName('bms_advancedstock_stock_movement')),
                array('qty' => 'sum(if(tbl_stock_movement.sm_from_warehouse_id = ' . $warehouseId . ', -tbl_stock_movement.sm_qty, tbl_stock_movement.sm_qty))'))
            ->where('(tbl_stock_movement.sm_from_warehouse_id = ' . $warehouseId . ' OR tbl_stock_movement.sm_to_warehouse_id = ' . $warehouseId . ') AND (sm_from_warehouse_id <> sm_to_warehouse_id)')
            ->where('tbl_stock_movement.sm_created_at <= "' . $maxDate.'"')
            ->where('tbl_stock_movement.sm_product_id = ' . $productId);

        $value = $this->_reportResource->getDbConnection()->fetchOne($sql);
        if ($value < 0)
            $value = 0;
        return $value;
    }

}