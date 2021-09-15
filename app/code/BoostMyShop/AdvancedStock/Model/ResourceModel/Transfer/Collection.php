<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Transfer', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer');
    }

}
