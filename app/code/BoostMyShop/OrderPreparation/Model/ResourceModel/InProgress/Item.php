<?php

namespace BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress;


class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_orderpreparation_inprogress_item', 'ipi_id');
    }

}
