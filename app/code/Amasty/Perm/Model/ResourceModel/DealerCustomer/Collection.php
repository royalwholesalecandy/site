<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\ResourceModel\DealerCustomer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Perm\Model\DealerCustomer', 'Amasty\Perm\Model\ResourceModel\DealerCustomer');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addDealersToSelect(array $customersIds)
    {
        $this->getSelect()
            ->joinLeft(
                ['amasty_perm_dealer' => $this->getTable('amasty_perm_dealer')],
                'amasty_perm_dealer.entity_id = main_table.dealer_id',
                []
            )
            ->joinLeft(
                ['user' => $this->getTable('admin_user')],
                'user.user_id = amasty_perm_dealer.user_id',
                ['contactname' => 'concat(user.firstname, " ", user.lastname)']
            );

        if (count($customersIds) > 0) {
            $this->addFieldToFilter('main_table.customer_id', ['in' => $customersIds]);
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

    public function getCustomersIds()
    {
        $ids = [];
        foreach($this->getItems() as $dealerCustomer){
            $ids[] = $dealerCustomer->getCustomerId();
        }
        return $ids;
    }
}