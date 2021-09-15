<?php

namespace BoostMyShop\Margin\Model\ResourceModel\Cogs;


class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
        $this->setRowIdFieldName('item_id');
        $this->_initTables();
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields()
    {
        $this->addAttributeToSelect('name');
        $this->addAttributeToSelect('sku');
        $this->addAttributeToSelect('status');

        $this->getSelect()->join(
            ['soi' => $this->getTable('sales_order_item')],
            '(entity_id = soi.product_id)',
            ['qty_invoiced', 'order_id', 'base_cost', 'row_invoiced', 'base_price', 'product_type', 'parent_item_id', 'item_id']
        );

        $this->getSelect()->join(
            ['sog' => $this->getTable('sales_order_grid')],
            '(sog.entity_id = soi.order_id)',
            ['order_date' => 'created_at', 'increment_id', 'order_store_id' => 'store_id', 'billing_name']
        );

        $this->getSelect()->where('qty_invoiced > 0');

        return $this;
    }

    /**
     * Set order to attribute
     *
     * @param string $attributea
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = 'DESC')
    {
        switch ($attribute) {
            case 'order_date':
                $this->getSelect()->order('soi.created_at ' . $dir);
                break;
            case 'order_store_id':
                $this->getSelect()->order('soi.store_id ' . $dir);
                break;
            case 'increment_id':
            case 'qty_invoiced':
            case 'base_cost':
            case 'base_price':
            case 'row_invoiced':
            case 'billing_name':
                $this->getSelect()->order($attribute.' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
                break;
        }
        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param AbstractAttribute|string $attribute
     * @param array|null $condition
     * @param string $joinType
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'order_date':
                $conditionSql = $this->_getConditionSql('soi.created_at', $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'order_store_id':
                $conditionSql = $this->_getConditionSql('soi.store_id', $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'qty_invoiced':
            case 'base_cost':
            case 'base_price':
            case 'row_invoiced':
            case 'increment_id':
            case 'billing_name':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
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

}
