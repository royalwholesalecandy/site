<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\ResourceModel\Dealer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Perm\Model\Dealer', 'Amasty\Perm\Model\ResourceModel\Dealer');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addUserData()
    {
        $this
            ->join(
                ['user' => $this->getTable('admin_user')],
                'user.user_id = main_table.user_id',
                ['contactname' => 'concat(user.firstname, " ", user.lastname)', 'email']
            )
            ->join(
                ['amasty_perm_role' => $this->getTable('amasty_perm_role')],
                'amasty_perm_role.entity_id = main_table.role_id',
                []
            )->join(
                ['authorization_role' => $this->getTable('authorization_role')],
                'authorization_role.parent_id = amasty_perm_role.role_id
                and authorization_role.user_id = main_table.user_id',
                []
            );

        $this->getSelect()
            ->where('main_table.role_id is not null')
            ->group('main_table.user_id');

        return $this;
    }

    public function toUserOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'contactname');
    }
}