<?php

namespace BoostMyShop\Organizer\Model\ResourceModel;


class Organizer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_organizer', 'o_id');
    }


}
