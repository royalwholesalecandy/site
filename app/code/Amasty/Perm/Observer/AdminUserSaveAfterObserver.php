<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminUserSaveAfterObserver implements ObserverInterface
{
    protected $_dealerFactory;
    protected $_roleFactory;
    protected $_request;

    public function __construct(
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        \Magento\Framework\App\Request\Http $request
    ){
        $this->_dealerFactory = $dealerFactory;
        $this->_roleFactory = $roleFactory;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $user = $observer->getEvent()->getData('data_object');

        if ($user){
            $dealer = $this->_dealerFactory->create()->load($user->getId(), 'user_id');
            $role = $this->_roleFactory->create()
                ->load($user->getRole()->getId(), 'role_id');

            if ($role->getId()) {

                $data = [];
                if ($perm = $this->_request->getParam('amasty_perm')){
                    $data = $perm;
                    if (isset($data['customer_group_ids']) && is_array($data['customer_group_ids'])) {
                        $data['customer_group_ids'] = implode(',', $data['customer_group_ids']);
                    } else {
                        $data['customer_group_ids'] = '';
                    }
                }

                $dealer->addData(array_merge([
                    'user_id' => $user->getId(),
                    'role_id' => $role->getId()
                ], $data));

                $dealer->save();

                if (array_key_exists('in_dealer_customer', $data)){
                    $customersIds = $data['in_dealer_customer'];
                    parse_str($customersIds, $customersIds);
                    $customersIds = array_keys($customersIds);

                    $dealer->saveCustomers($customersIds);
                }
            }
        }
    }
}