<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Replenishment;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    public function init()
    {
        $this->addAttributeToSelect('name');
        $this->addAttributeToSelect('status');
        $this->addAttributeToSelect('thumbnail');
        $this->addAttributeToSelect('qty_to_receive');

        $this->addFieldToFilter('type_id', array('in' => array('simple')));

        $productIds = array_merge($this->getBackorderProductIds(), $this->getLowStockProductIds());
        if (count($productIds) > 0)
            $this->addFieldToFilter('entity_id', array('in' => $productIds));
        else
            $this->addFieldToFilter('entity_id', array('in' => [-1]));

        $this->joinAdditionalFields();

        $this->joinSupplierData();


        return $this;
    }

    public function getExpression($expr)
    {
        $exprBackorder = 'CASE  WHEN qty < 0 THEN -TRUNCATE(qty, 0) ELSE 0 END';
        $defaultConfigNotifyStockQty = 1;
        $exprLowStock = 'TRUNCATE(if (use_config_notify_stock_qty = 1, '.$defaultConfigNotifyStockQty.' - if(qty > 0, qty, 0), notify_stock_qty - if(qty > 0, qty, 0)), 0)';
        $exprToReceive = 'if ({{qty_to_receive}}, {{qty_to_receive}}, 0)';

        $expQtyToOrder = '(if ('.$exprBackorder.' + '.$exprLowStock.' - '.$exprToReceive.' > 0, '.$exprBackorder.' + '.$exprLowStock.' - '.$exprToReceive.', 0))';
        $expQtyToOrder = str_replace('{{qty_to_receive}}', 'at_qty_to_receive.value', $expQtyToOrder);

        $exprReason = '
                        CASE
                            WHEN '.$exprBackorder.' - '.$exprToReceive.' > 0 THEN \'backorder\'
                            WHEN '.$exprLowStock.' - '.$exprBackorder.' - '.$exprToReceive.' > 0 THEN \'lowstock\'
                            WHEN '.$exprLowStock.' + '.$exprBackorder.' <= '.$exprToReceive.' THEN \'waiting_for_reception\'
                            ELSE \'undefined\'
                        END
                        ';
        $exprReason = str_replace(')', ') ', $exprReason);
        $exprReason = trim(str_replace("\n", "", $exprReason));
        $exprReason = str_replace('{{qty_to_receive}}', 'at_qty_to_receive.value', $exprReason);

        switch($expr)
        {
            case 'backorder':
                return $exprBackorder;
                break;
            case 'lowstock':
                return $exprLowStock;
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
        }
    }

    public function joinAdditionalFields()
    {
        $this->getSelect()->join($this->getTable('cataloginventory_stock_item'), 'product_id = entity_id and website_id = 0');

        $this->getSelect()->columns(['qty_for_backorder' => new \Zend_Db_Expr($this->getExpression('backorder'))]);

        $this->getSelect()->columns(['qty_for_low_stock' => new \Zend_Db_Expr($this->getExpression('lowstock'))]);

        $this->addExpressionAttributeToSelect('qty_to_order', $this->getExpression('toorder'), ['qty_to_receive']);

        $this->addExpressionAttributeToSelect('reason', new \Zend_Db_Expr($this->getExpression('reason')), []);


    }

    public function joinSupplierData()
    {

        $this->getSelect()
            ->joinLeft(
                ['sp' => $this->getTable('bms_supplier_product')],
                '(sp_product_id = e.entity_id and sp_primary = 1)',
                []
            )
            ->joinLeft(
                ['sup' => $this->getTable('bms_supplier')],
                '(sp_sup_id = sup_id)',
                []
            )
            ->columns(['supply_shipping' => new \Zend_Db_Expr('IF(sp_shipping_delay, sp_shipping_delay, sup_shipping_delay) + IF(sp_supply_delay, sp_supply_delay, sup_supply_delay)')])
        ;
    }


    public function getBackorderProductIds()
    {
        $mySelect = clone $this->getSelect();
        $mySelect->reset()->from($this->getTable('cataloginventory_stock_item'), ['product_id'])->where("qty < 0");
        return $this->getConnection()->fetchCol($mySelect);
    }

    public function getLowStockProductIds()
    {
        //todo : retrieve value from configuration
        $notifyStockQuantity = 1;

        $mySelect = clone $this->getSelect();
        $mySelect->reset()->from($this->getTable('cataloginventory_stock_item'), ['product_id'])->where("(use_config_notify_stock_qty = 1 and qty < ".$notifyStockQuantity.") OR (use_config_notify_stock_qty = 0 and qty < notify_stock_qty)");
        $ids = $this->getConnection()->fetchCol($mySelect);
        return $ids;
    }

    public function addProductFilter($productId)
    {
        $this->addFieldToFilter('entity_id', $productId);
        return $this;
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();

        $subQuery =  new \Zend_Db_Expr('('.$this->getSelect().')');

        $select = $this->getConnection()
            ->select()
            ->from($subQuery)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(new \Zend_Db_Expr('count(*)'))
        ;
        
         return $select;
    }

    public function setOrder($attribute, $dir = 'DESC')
    {
        switch ($attribute) {
            case 'sku':
            case 'name':
            case 'status':
                parent::setOrder($attribute, $dir);
                break;
            default:
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;

        }
        return $this;
    }

        public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'qty_for_backorder':
                $conditionSql = $this->_getConditionSql($this->getExpression('backorder'), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'qty_for_low_stock':
                $conditionSql = $this->_getConditionSql($this->getExpression('lowstock'), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'qty_to_order':
                $conditionSql = $this->_getConditionSql($this->getExpression('toorder'), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'reason':
                $conditionSql = $this->_getConditionSql($this->getExpression('reason'), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

    //FONCTIONS FOR PLUGIN COMPATIBILITY

    public function parentAddAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        parent::addAttributeToFilter($attribute, $condition, $joinType);
    }

    public function getConditionSql($fieldName, $condition)
    {
        return $this->_getConditionSql($fieldName, $condition);
    }
}
