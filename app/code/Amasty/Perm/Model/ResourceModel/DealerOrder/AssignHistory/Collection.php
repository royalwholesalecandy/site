<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\ResourceModel\DealerOrder\AssignHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Perm\Model\DealerOrder\AssignHistory', 'Amasty\Perm\Model\ResourceModel\DealerOrder\AssignHistory');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function setDealerOrderFilter(\Amasty\Perm\Model\DealerOrder $dealerOrder)
    {
        $this->addFieldToFilter('parent_id', $dealerOrder->getOrderId());

        return $this;
    }
}