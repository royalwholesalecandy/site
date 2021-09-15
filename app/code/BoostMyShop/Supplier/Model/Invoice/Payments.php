<?php

namespace BoostMyShop\Supplier\Model\Invoice;


class Payments extends \Magento\Framework\Model\AbstractModel
{
    
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Invoice\Payments');
    }
    
}
