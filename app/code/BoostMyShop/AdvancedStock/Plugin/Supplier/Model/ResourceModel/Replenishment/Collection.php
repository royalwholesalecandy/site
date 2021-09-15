<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model\ResourceModel\Replenishment;


class Collection
{
    protected $_config;

    protected $_warehouseItemCollectionFactory;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory
    ){
        $this->_config = $config;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
    }

    public function aroundGetExpression(\BoostMyShop\Supplier\Model\ResourceModel\Replenishment\Collection $subject, $proceed, $expr)
    {
        $defaultWarningStockLevel = $this->_config->getDefaultWarningStockLevel();
        $defaultIdealStockLevel = $this->_config->getDefaultIdealStockLevel();

        $exprToReceive = 'if ({{qty_to_receive}}, {{qty_to_receive}}, 0)';

        $backorderExpr = 'SUM(if(wi_quantity_to_ship > wi_physical_quantity, wi_quantity_to_ship - wi_physical_quantity, 0))';
        $lowStockExpr = 'SUM(if(wi_available_quantity < if (wi_use_config_warning_stock_level, '.$defaultWarningStockLevel.', wi_warning_stock_level), if (wi_use_config_ideal_stock_level, '.$defaultIdealStockLevel.', wi_ideal_stock_level) - wi_available_quantity, 0))';

        $expQtyToOrder = '(if ('.$backorderExpr.' + '.$lowStockExpr.' - '.$exprToReceive.' > 0, '.$backorderExpr.' + '.$lowStockExpr.' - '.$exprToReceive.', 0))';
        $expQtyToOrder = str_replace('{{qty_to_receive}}', 'at_qty_to_receive.value', $expQtyToOrder);

        $exprReason = '
                        CASE
                            WHEN '.$backorderExpr.' - '.$exprToReceive.' > 0 THEN \'backorder\'
                            WHEN '.$lowStockExpr.' + '.$backorderExpr.' - '.$exprToReceive.' > 0 THEN \'lowstock\'
                            WHEN '.$lowStockExpr.' + '.$backorderExpr.' <= '.$exprToReceive.' THEN \'waiting_for_reception\'
                            ELSE \'undefined\'
                        END
                        ';
        $exprReason = str_replace(')', ') ', $exprReason);
        $exprReason = trim(str_replace("\n", "", $exprReason));
        $exprReason = str_replace('{{qty_to_receive}}', 'at_qty_to_receive.value', $exprReason);

        $totalWeek = 0;
        for($i=1;$i<=3;$i++)
            $totalWeek += (int)$this->_config->getSetting('stock_level/history_range_'.$i);

        switch($expr)
        {
            case 'backorder':
                return $backorderExpr;
                break;
            case 'lowstock':
                return $lowStockExpr;
                break;
            case 'toreceive':
                return $exprToReceive;
                break;
            case 'toorder':
                return $expQtyToOrder;
                break;
            case 'reason':
                return $exprReason;
                break;
            case 'average_sale':
                if ($totalWeek > 0)
                    return 'truncate((SUM(sh_range_1) +  SUM(sh_range_2) + SUM(sh_range_3)) / '.$totalWeek.', 1)';
                else
                    return '0';
                break;
            case 'run_out':
                if ($totalWeek > 0)
                    return 'truncate( sum(wi_available_quantity) + at_qty_to_receive.value / ((SUM(sh_range_1) +  SUM(sh_range_2) + SUM(sh_range_3)) / '.$totalWeek.' / 7), 0)';
                else
                    return '0';
                break;
        }
    }

    public function aroundGetBackorderProductIds(\BoostMyShop\Supplier\Model\ResourceModel\Replenishment\Collection $subject, $proceed)
    {
        $productIds = $this->_warehouseItemCollectionFactory
                            ->create()
                            ->joinWarehouse(true)
                            ->addBackorderFilter()
                            ->getProductIds();

        return $productIds;

        /*
        $mySelect = clone $subject->getSelect();
        $mySelect->reset()
                ->from($subject->getTable('bms_advancedstock_warehouse_item'), ['wi_product_id'])
                ->join($subject->getTable('bms_advancedstock_warehouse'), 'w_id = wi_warehouse_id and w_use_in_supplyneeds = 1')
                ->where("wi_quantity_to_ship > wi_physical_quantity");

        $productIds = $subject->getConnection()->fetchCol($mySelect);
        return $productIds;
        */
    }

    public function aroundGetLowStockProductIds(\BoostMyShop\Supplier\Model\ResourceModel\Replenishment\Collection $subject, $proceed)
    {
        $defaultWarningStockLevel = $this->_config->getDefaultWarningStockLevel();

        $productIds = $this->_warehouseItemCollectionFactory
                            ->create()
                            ->joinWarehouse(true)
                            ->addLowStockFilter($defaultWarningStockLevel)
                            ->getProductIds();

        return $productIds;

        /**
        $mySelect = clone $subject->getSelect();
        $mySelect->reset()
                ->from($subject->getTable('bms_advancedstock_warehouse_item'), ['wi_product_id'])
                ->join($subject->getTable('bms_advancedstock_warehouse'), 'w_id = wi_warehouse_id and w_use_in_supplyneeds = 1')
                ->where("wi_available_quantity < if(wi_use_config_warning_stock_level = 1, ".$defaultWarningStockLevel.", wi_warning_stock_level)");
        $productIds = $subject->getConnection()->fetchCol($mySelect);

        return $productIds;
         */
    }

    public function aroundJoinAdditionalFields(\BoostMyShop\Supplier\Model\ResourceModel\Replenishment\Collection $subject, $proceed)
    {

        $subject->getSelect()->join($subject->getTable('bms_advancedstock_warehouse_item'), 'wi_product_id = entity_id');
        $subject->getSelect()->join($subject->getTable('bms_advancedstock_warehouse'), 'w_id = wi_warehouse_id and w_use_in_supplyneeds = 1');

        $subject->getSelect()->columns(['qty_for_backorder' => new \Zend_Db_Expr($subject->getExpression('backorder'))]);
        $subject->getSelect()->columns(['qty_for_low_stock' => new \Zend_Db_Expr($subject->getExpression('lowstock'))]);

        $subject->addExpressionAttributeToSelect('qty_to_order', $subject->getExpression('toorder'), ['qty_to_receive']);
        $subject->addExpressionAttributeToSelect('reason', new \Zend_Db_Expr($subject->getExpression('reason')), []);

        //sales history
        $subject->getSelect()->joinLeft($subject->getTable('bms_advancedstock_sales_history'), 'wi_id = sh_warehouse_item_id');
        $subject->getSelect()->columns(['sales_history' => new \Zend_Db_Expr('concat(SUM(sh_range_1), "/", SUM(sh_range_2), "/", SUM(sh_range_3))')]);

        $subject->getSelect()->columns(['avg_sales' => new \Zend_Db_Expr($subject->getExpression('average_sale'))]);
        $subject->getSelect()->columns(['run_out' => new \Zend_Db_Expr($subject->getExpression('run_out'))]);

        $subject->getSelect()->group('sku');

        return $this;
    }


    public function aroundAddAttributeToFilter(\BoostMyShop\Supplier\Model\ResourceModel\Replenishment\Collection $subject, $proceed, $attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'qty_for_backorder':
                $conditionSql = $subject->getConditionSql($subject->getExpression('backorder'), $condition);
                $subject->getSelect()->having($conditionSql);
                break;
            case 'qty_for_low_stock':
                $conditionSql = $subject->getConditionSql('qty_for_low_stock', $condition);
                $conditionSql = str_replace("'", "", $conditionSql);
                $subject->getSelect()->having($conditionSql);
                break;
            case 'qty_to_order':
                $conditionSql = $subject->getConditionSql($subject->getExpression('toorder'), $condition);
                $subject->getSelect()->having($conditionSql);
                break;
            case 'reason':
                $conditionSql = $subject->getConditionSql($subject->getExpression('reason'), $condition);
                $subject->getSelect()->having($conditionSql);
                break;
            case 'avg_sales':
                $conditionSql = $subject->getConditionSql($subject->getExpression('average_sale'), $condition);
                $subject->getSelect()->having($conditionSql);
                break;
            case 'run_out':
                $conditionSql = $subject->getConditionSql($subject->getExpression('run_out'), $condition);
                $subject->getSelect()->having($conditionSql);
                break;
            default:
                $subject->parentAddAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }


}
