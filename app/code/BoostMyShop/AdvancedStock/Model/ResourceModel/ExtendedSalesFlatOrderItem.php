<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class ExtendedSalesFlatOrderItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    protected function _construct()
    {
        $this->_init('bms_advancedstock_extended_sales_flat_order_item', 'esfoi_id');
    }

}
