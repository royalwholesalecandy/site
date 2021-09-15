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

class RegisterSuccess extends \Itoris\PendingRegistration\Observers\Register
{
    public function execute(\Magento\Framework\Event\Observer $observer){
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = $this->getDataHelper();

        if (!($helper->isRegFieldsActive() || $helper->isStoreLoginControlRequireLogin())) {
            return;
        }
        /** @var $controller \Itoris\RegFields\AccountController */
        $controller = $observer->getAccountController();
        /** @var $customer \Magento\Customer\Model\Data\Customer */
        $customer = $observer->getCustomer();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());
        if (!$controller->getRequest()->getParam('email')) {
            return;
        }
        $helper->init(null);
        $scope = $helper->getFrontendScope();

        if (!\Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope)) {
            return;
        }
        if (!$helper->isEnabled()) {
            return;
        }

        $isRegFieldsEnable = false;
        if($helper->isRegFieldsActive()){
            $isRegFieldsEnable = (bool)$this->getDataHelper()->getRegFieldsHelper()->isEnabled();
        }

        $session = $this->getCustomerSession();
        $message = null;
        if ($customer->getConfirmation() && $customerModel->isConfirmationRequired()) {
            $customerModel->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $this->getStoreManager()->getStore()->getId()
            );
           // if(!$isRegFieldsEnable ){
            $message = __('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                $this->getCustomerUrl()->getEmailConfirmationUrl($customer->getEmail()));

            $message=htmlspecialchars($message);
                if(($this->getRequest()->getParam('itorisAjaxLogin') || (int) $this->getRequest()->getParam('itorisAjaxLogin'))){
                    setcookie('itoris_pending_confirmation_itoris', $message);
                }else{
                    $message = __('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email  please use the link %1',
                        $this->getCustomerUrl()->getEmailConfirmationUrl($customer->getEmail()));
                    $this->getMessageManager()->addSuccessMessage($message);

                }
           // }
            $message = null;
        } else {
            if($this->getRequest()->getParam('itorisAjaxLogin') || (int) $this->getRequest()->getParam('itorisAjaxLogin') ){
                setcookie('itoris_pending', true);
            }else{
                $message = __('Thank You for creating an account with us on RoyalWholesalecandy.com. Your account is pending approval and will be reviewed within 24 hours. You will receive another email once your account is confirmed. Any questions please contact us at 1-888-261-8277 or Chat with us!');
                $customerModel->setPendingMessage($message);
            }

        }


        $status = $this->writePendingStatusInDb($controller->getRequest()->getParam('email'), $scope, $helper);
        if ($status != \Itoris\PendingRegistration\Model\Users::STATUS_APPROVED && $message) {
            $this->getMessageManager()->addSuccess($message);
        }
        if ($status == \Itoris\PendingRegistration\Model\Users::STATUS_APPROVED && $controller instanceof \Itoris\RegFields\AccountController) {
            return;
        }

        $this->getEventManager()->dispatch('controller_action_postdispatch_customer_account_createpost',
            ['account_controller' => $controller, 'request' => $this->getRequest()]
        );
        if ($helper->isStoreLoginControlRequireLogin() || $this->getCustomerSession()->getSmartLoginRegistration()) {
            if ($status != \Itoris\PendingRegistration\Model\Users::STATUS_APPROVED) {
                $customerModel->setPendingMessage($message);
            }
        } else {
            $helper->customerLogout($observer->getControllerAction());
        }
    }
}