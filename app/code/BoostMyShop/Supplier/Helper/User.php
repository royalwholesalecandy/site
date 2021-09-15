<?php

namespace BoostMyShop\Supplier\Helper;

class User {

    protected $_userList;

    public function __construct(\Magento\User\Model\ResourceModel\User\CollectionFactory $userList)
    {
        $this->_userList = $userList;
    }

    public function getAllowedUsersForPurchaseOrders()
    {
        /** @var $collection \Magento\User\Model\ResourceModel\User\Collection */
        $collection = $this->_userList->create();

        return $collection->join(array('arole' => $collection->getTable('authorization_role')),'arole.user_id=main_table.user_id','parent_id')
            ->join(array('arule' => $collection->getTable('authorization_rule')),'arule.role_id=arole.parent_id')
            ->addFilter('is_active', 1)
            ->addFieldToFilter('resource_id',array('in' => array('BoostMyShop_Supplier::purchase_orders','Magento_Backend::all')))
            ->addFilter('permission','allow')
            ->setOrder('firstname', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

}