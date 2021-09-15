<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PendingRegistration\Observers\Register;

class SendEmails extends \Itoris\PendingRegistration\Observers\Register
{
    public function execute(\Magento\Framework\Event\Observer $observer){
        if (empty($this->getRequest()->getParam('email'))) {
            return;
        }
        $bool=false;
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = $this->getDataHelper();
        $helper->init(null);
        $scope = $helper->getFrontendScope();

        if (!\Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope)) {
            return;
        }

        if (!$helper->isEnabled()) {
            return;
        }
        $controller = null;
        if ($observer instanceof \Magento\Framework\Event\Observer) {
            $controller = $observer->getData('controller_action');
        }
        $this->showSuccessPendingMessage = false;
        if($this->getDataHelper()->isRegFieldsActive() && $this->getDataHelper()->getRegFieldsHelper()->isEnabled()){
            $bool=true;
            $this->createCustomerReg();

        }else{
            $this->createCustomer($controller);
        }
        if(($this->getRequest()->getParam('itorisAjaxLogin') || !(int) $this->getRequest()->getParam('itorisAjaxLogin') && $this->_objectManager->create('Itoris\PendingRegistration\Helper\MagentoVersion')->getMagentoVersion()>2.07) && $bool){
            $this->getEventManager()->dispatch('itorias_pending_ajax_register');
        }
        $status = $this->writePendingStatusInDb($this->getRequest()->getParam('email'), $scope, $helper);
        if ($status != 1 && $this->showSuccessPendingMessage) {
            $this->getMessageManager()->addSuccess(__('Thank you for registration. Your account requires moderation before you can login.'));

        }
        $helper->customerLogout($observer->getControllerAction());
    }
}