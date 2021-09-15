<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Transit;


class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    public function init($withQtyToReceiveFilter = true)
    {
        $this->addAttributeToSelect('name');
        $this->addAttributeToSelect('qty_to_receive');

        if ($withQtyToReceiveFilter)
            $this->addFieldToFilter('qty_to_receive', ['gt' => 0]);

        $this->getSelect()->join($this->getTable('bms_purchase_order_product'), 'e.entity_id = pop_product_id');
        $this->getSelect()->join($this->getTable('bms_purchase_order'), 'pop_po_id = po_id', new \Zend_Db_Expr('MIN(if(pop_eta, pop_eta, po_eta)) as eta'));
        $this->getSelect()->where('po_status = "'.\BoostMyShop\Supplier\Model\Order\Status::expected.'"');
        $this->getSelect()->where('pop_qty > pop_qty_received');


        $this->getSelect()->group('e.entity_id');

        return $this;
    }

    public function addSupplierFilter($supplierId)
    {
        $this->getSelect()->where('po_sup_id = '.$supplierId);
        return $this;
    }

    public function getAllProductIds()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns(new \Zend_Db_Expr('distinct pop_product_id'));
        $result = $this->getConnection()->fetchCol($this->getSelect());
        return $result;
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();

        if(count($this->getSelect()->getPart('group')) > 0) {
            $countSelect = clone $this->getSelect();
            $countSelect->reset('order');
            $countSelect->reset('limitcount');
            $countSelect->reset('limitoffset');
            $countSelect->reset('columns');
            $countSelect->reset('group');
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart('group');
            $countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
        }
        else
        {
            $countSelect = is_null($select) ? $this->_getClearSelect() : $this->_buildClearSelect($select);
            $countSelect->columns('COUNT(DISTINCT e.entity_id)');
            if ($resetLeftJoins) {
                $countSelect->resetJoinLeft();
            }
        }

        return $countSelect;
    }

    public function setOrder($attribute, $dir = 'DESC')
    {
        
        switch ($attribute) {
            case 'eta':
            case 'backorder_qty':   //should not be here as backorder_qty is introduced by advancedstock module... not a big deal anyway
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
                break;
        }
        return $this;
    }


    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch($field)
        {
            case 'eta':
                if(array_key_exists('from', $condition))
                    $this->_select->having('MIN(if(pop_eta, pop_eta, po_eta)) >= \''.$condition['from']->format('Y-m-d 00:00:00').'\'');

                if(array_key_exists('to', $condition))
                    $this->_select->having('MIN(if(pop_eta, pop_eta, po_eta)) <= \''.$condition['to']->format('Y-m-d 00:00:00').'\'');

                break;
            default:
                parent::addFieldToFilter($field, $condition);
                break;
        }


        return $this;
    }
}
