<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Invoice\Payments;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Invoice\Payments', 'BoostMyShop\Supplier\Model\ResourceModel\Invoice\Payments');
    }

    public function addSupplierInvoiceDetails()
    {
        $this->getSelect()
            ->join($this->getTable('bms_supplier_invoice'), 'bsip_invoice_id = bsi_id')
            ->join($this->getTable('bms_supplier'), 'bsi_sup_id = sup_id');
        return $this;

    }

    public function addInvoiceFilter($invoiceId)
    {
        $this->getSelect()->where("bsip_invoice_id = ".$invoiceId);
        return $this;
    }

}
