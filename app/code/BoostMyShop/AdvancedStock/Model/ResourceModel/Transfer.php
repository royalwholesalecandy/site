<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel;


class Transfer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_transfer', 'st_id');
    }


}
