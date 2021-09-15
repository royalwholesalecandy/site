<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\SalesHistory;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\SalesHistory', 'BoostMyShop\AdvancedStock\Model\ResourceModel\SalesHistory');
    }

    public function addProductFilter($productId)
    {
        $this->getSelect()->join(
            ['tbl_warehouse_item' => $this->getTable('bms_advancedstock_warehouse_item')],
            'main_table.sh_warehouse_item_id = tbl_warehouse_item.wi_id',
            []
        );
        $this->getSelect()->where('wi_product_id = '.$productId);


        return $this;
    }

}
