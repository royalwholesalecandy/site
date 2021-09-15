<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Invoice;


class Payments extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_supplier_invoice_payments', 'bsip_id');
    }

}
