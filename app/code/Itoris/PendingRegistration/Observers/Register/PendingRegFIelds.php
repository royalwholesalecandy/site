<?php
/**
 * Created by PhpStorm.
 * User: Workstation1
 * Date: 06.07.2016
 * Time: 18:00
 */

namespace Itoris\PendingRegistration\Observers\Register;


class PendingRegFIelds extends \Itoris\PendingRegistration\Observers\Register
{
    public function execute(\Magento\Framework\Event\Observer $observer){
        $helper = $this->getDataHelper();
        $helper->init(null);
        $helper->customerCreate($observer->getControllerAction());
    }
}