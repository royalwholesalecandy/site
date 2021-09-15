<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class StockMovement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_stock_movement', 'sm_id');
    }


}
