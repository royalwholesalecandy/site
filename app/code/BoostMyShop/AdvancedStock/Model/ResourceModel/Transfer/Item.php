<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer;


class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_advancedstock_transfer_item', 'sti_id');
    }


}
