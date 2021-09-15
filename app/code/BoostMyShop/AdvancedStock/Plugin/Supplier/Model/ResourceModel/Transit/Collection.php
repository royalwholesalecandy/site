<?php

namespace BoostMyShop\AdvancedStock\Plugin\Supplier\Model\ResourceModel\Transit;


class Collection
{
    public function aroundInit(\BoostMyShop\Supplier\Model\ResourceModel\Transit\Collection $subject, $proceed)
    {
        $collection = $proceed();

        $collection->getSelect()->joinLeft(
            ['sh' => $collection->getTable('bms_advancedstock_warehouse_item')],
            'wi_product_id = pop_product_id',
            new \Zend_Db_Expr('SUM(distinct if(wi_quantity_to_ship <= wi_physical_quantity, 0, wi_quantity_to_ship - wi_physical_quantity)) as backorder_qty')
        );

        return $collection;
    }

    public function aroundAddFieldToFilter(\BoostMyShop\Supplier\Model\ResourceModel\Transit\Collection $subject, $proceed, $field, $condition = null)
    {
        switch($field)
        {
            case 'backorder_qty':
                if(array_key_exists('from', $condition))
                    $subject->getSelect()->having('SUM(distinct if(wi_quantity_to_ship <= wi_physical_quantity, 0, wi_quantity_to_ship - wi_physical_quantity)) >= '.$condition['from']);
                if(array_key_exists('to', $condition))
                    $subject->getSelect()->having('SUM(distinct if(wi_quantity_to_ship <= wi_physical_quantity, 0, wi_quantity_to_ship - wi_physical_quantity)) <= '.$condition['to']);
                break;
            default:
                $proceed($field, $condition); //call parent...
                break;
        }
    }

}
