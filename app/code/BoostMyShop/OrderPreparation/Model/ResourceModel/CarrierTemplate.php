<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel;


class CarrierTemplate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_orderpreparation_carrier_template', 'ct_id');
    }


}
