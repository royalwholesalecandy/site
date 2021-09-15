<?php

namespace BoostMyShop\AdvancedStock\Model;


class StockMovementLogs extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovementLogs');
    }

    public function prune()
    {
        return $this->_getResource()->prune();
    }

}