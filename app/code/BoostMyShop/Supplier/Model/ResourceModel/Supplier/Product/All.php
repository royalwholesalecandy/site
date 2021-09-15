<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product;


class All extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
        $this->setRowIdFieldName('entity_id');
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

    protected function _joinFields()
    {
        $this->addAttributeToSelect('name')->addAttributeToSelect('sku')->addAttributeToSelect('status');

        $this->getSelect()->joinLeft(
            ['sp' => $this->getTable('bms_supplier_product')],
            '(sp_product_id = e.entity_id)',
            [
                '*',
                'IF(sp_id > 0, 1, 0) as associated',
            ]
        );

        return $this;
    }

    public function addSupplierFilter($supplierId)
    {
        //todo : doesnt return record if product associated to another supplier !
        $this->getSelect()->where(' (sp_id is null OR sp_sup_id = '.$supplierId.')');
        return $this;
    }

    public function addAssociatedFilter()
    {
        $this->getSelect()->where(' (sp_id > 0)');
        return $this;
    }

    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'sp_sku':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'associated':
                $this->getSelect()->where('sp_id > 0');
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

    public function setOrder($attribute, $dir = 'DESC')
    {
        switch ($attribute) {
            case 'sp_sku':
            case 'sp_price':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
                break;
        }
        return $this;
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = is_null($select) ? $this->_getClearSelect() : $this->_buildClearSelect($select);
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        //if ($resetLeftJoins) {
        //    $countSelect->resetJoinLeft();
        //}
        return $countSelect;
    }

    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);
        //$idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

}
