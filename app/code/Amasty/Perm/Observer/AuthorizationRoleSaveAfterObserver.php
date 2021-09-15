<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Observer;

use Magento\Framework\Event\ObserverInterface;

class AuthorizationRoleSaveAfterObserver implements ObserverInterface
{
    protected $_roleFactory;
    protected $_request;

    public function __construct(
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        \Magento\Framework\App\Request\Http $request
    ){
        $this->_roleFactory = $roleFactory;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $systemRole = $observer->getEvent()->getData('data_object');

        if ($systemRole){
            $role = $this->_roleFactory->create()->load($systemRole->getId(), 'role_id');
            if ($this->_request->getParam('amasty_perm_is_dealer')) {
                $role->addData([
                    'role_id' => $systemRole->getId()
                ]);
                $role->save();
            } else if ($this->_request->getParam('amasty_perm_is_dealer') === '0') {
                $role->delete();
            }
        }
    }
}
