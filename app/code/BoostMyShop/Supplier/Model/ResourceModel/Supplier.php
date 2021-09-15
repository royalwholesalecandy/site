<?php

namespace BoostMyShop\Supplier\Model\ResourceModel;


class Supplier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_supplier', 'sup_id');
    }


}
