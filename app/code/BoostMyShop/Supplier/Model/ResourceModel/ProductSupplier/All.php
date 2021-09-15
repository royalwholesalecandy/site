<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\ProductSupplier;


class All extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
        $this->setRowIdFieldName('fake_id');
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

        $this->getSelect()->where('type_id = "simple"');

        $this->_joinFields();
        return $this;
    }

    public function addProductFilter($productId)
    {
        if (is_object($productId))
            $productId = $productId->getId();
        $this->addFieldToFilter('entity_id', $productId);
        return $this;
    }

    public function addAssociatedFilter()
    {
        $this->getSelect()->where('sp_id > 0');
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields()
    {
        $this->addAttributeToSelect('name')->addAttributeToSelect('sku')->addAttributeToSelect('status');

        $this->getSelect()->join(
            ['s' => $this->getTable('bms_supplier')],
            '',
            [
                'sup_id',
                'sup_currency',
                'sup_name',
                'concat(sup_id, \'_\', e.entity_id) as fake_id',
            ]
        );

        $this->getSelect()->joinLeft(
            ['sp' => $this->getTable('bms_supplier_product')],
            'entity_id = sp_product_id and sup_id = sp_sup_id',
            [
                '*',
                'if(sp_id > 0, 1, 0) as associated'
            ]
        );

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
            case 'sup_id':
            case 'sp_sku':
            case 'sp_price':
            case 'sp_moq':
            case 'sp_pack_qty':
            case 'sp_primary':
                $this->getSelect()->order($attribute . ' ' . $dir);
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
            case 'sup_id':
            case 'sp_sku':
            case 'sp_stock':
            case 'sp_price':
            case 'sp_moq':
            case 'sp_pack_qty':
            case 'sp_primary':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'associated':
                if ($condition['eq'] == "0")
                    $this->getSelect()->where('sp_id is null');
                else
                    $this->getSelect()->where('sp_id > 0');
                break;
            case 'fake_id':
                //nothing
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = is_null($select) ? $this->_getClearSelect() : $this->_buildClearSelect($select);
        $countSelect->columns('COUNT(DISTINCT concat(sup_id, \'_\', e.entity_id))');
        //if ($resetLeftJoins) {
        //    $countSelect->resetJoinLeft();
        //}
        return $countSelect;
    }

    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('concat(sup_id, \'_\', e.entity_id)');
        $idsSelect->limit($limit, $offset);
        //$idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

}
