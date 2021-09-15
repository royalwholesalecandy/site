<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\ResourceModel\DealerOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Perm\Model\DealerOrder', 'Amasty\Perm\Model\ResourceModel\DealerOrder');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addDealersToSelect(array $ordersIds)
    {
        if (count($ordersIds) > 0) {
            $this->addFieldToFilter('main_table.order_id', ['in' => $ordersIds]);
        }

        return $this;
    }

    public function getDealersIds()
    {
        $ids = [];
        foreach($this->getItems() as $dealerCustomer){
            $ids[] = $dealerCustomer->getDealerId();
        }
        return $ids;
    }
}