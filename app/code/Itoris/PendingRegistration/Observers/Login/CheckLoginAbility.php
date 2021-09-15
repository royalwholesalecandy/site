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

namespace Itoris\PendingRegistration\Observers\Login;

class CheckLoginAbility extends \Itoris\PendingRegistration\Observers\Observer
{
    public function execute(\Magento\Framework\Event\Observer $observer){
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = $this->_objectManager->create( 'Itoris\PendingRegistration\Helper\Data' );
        if (!$helper->isEnabled()) {
            return true;
        }

        $scope = $helper->getFrontendScope();
        if (!\Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope)) {
            return true;
        }
        $db = $this->getResourceConnection()->getConnection();
        /** @var int|null $cid */
        $cid = $this->getCustomerSession()->getId();

        $usersTableName = $this->getResourceConnection()->getTableName('itoris_pendingregistration_users');
        $status = $db->query('SELECT status FROM '.$usersTableName.' WHERE customer_id='.$cid);
        $status = $status->fetchColumn(0);
        $error = false;
        if (!$status) {
            $error = __('Thank You for creating an account with us on RoyalWholesalecandy.com. Your account is pending approval and will be reviewed within 24 hours. You will receive another email once your account is confirmed. Any questions please contact us at 1-888-261-8277 or Chat with us!');
        } elseif ($status == 2) {
            $error = __('Your registration has been declined. Please contact the site administrator for more details');
        } elseif ($status == \Itoris\PendingRegistration\Model\Users::STATUS_NOT_CONFIRMED_BY_EMAIL) {
            /** @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->getCustomerSession()->getCustomer();
            if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
                /** @var \Magento\Customer\Model\Url $customerUrlModel */
                $customerUrlModel = $this->_objectManager->create('Magento\Customer\Model\Url');
                $error = $this->getMessageManager()
                    ->addSuccess(
                        __('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                            $customerUrlModel->getEmailConfirmationUrl($customer->getEmail()))
                    );
            } else {
                $db->query('UPDATE '.$usersTableName.' SET status=0 WHERE customer_id='.$cid);
                $helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_ADMIN, $customer, $scope);
                $helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_USER, $customer, $scope);
              //  $error = __('');
            }
        }
        if ($error) {
            /** @var \Magento\Customer\Model\Session $session */
            $session = $this->getCustomerSession();
            $this->getMessageManager()->addError($error);
            $session->setCustomer($this->_objectManager->create('Magento\Customer\Model\Customer'));
            if ($this->getDataHelper()->isStoreLoginControlRequireLogin() || $session->getIsLoginViaSmartLogin()) {
                return;
            }
            $this->getResponse()->setRedirect($this->getUrlInterface()->getUrl('*/*/login'));
            if(($this->getRequest()->getParam('itorisAjaxLogin') || !(int) $this->getRequest()->getParam('itorisAjaxLogin') && $this->_objectManager->create('Itoris\PendingRegistration\Helper\MagentoVersion')->getMagentoVersion()>2.07)){
                $this->getEventManager()->dispatch('itorias_pending_ajax_login');
            }
            $this->getResponse()->sendHeaders();
            $helper->safeExit($observer->getControllerAction());
        }

        $this->getCustomerSession()->getCustomer()->setIsJustConfirmed(false);
        return true;
    }
}