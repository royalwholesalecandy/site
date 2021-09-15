<?php

namespace BoostMyShop\Supplier\Model\ResourceModel;


class Invoice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_supplier_invoice', 'bsi_id');
    }

}
