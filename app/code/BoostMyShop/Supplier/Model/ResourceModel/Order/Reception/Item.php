<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order\Reception;


class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_purchase_order_reception_item', 'pori_id');
    }

}
