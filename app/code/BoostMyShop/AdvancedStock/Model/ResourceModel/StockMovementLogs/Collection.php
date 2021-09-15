<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovementLogs;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\StockMovementLogs', 'BoostMyShop\AdvancedStock\Model\ResourceModel\StockMovementLogs');
    }

}