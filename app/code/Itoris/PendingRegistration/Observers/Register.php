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

namespace Itoris\PendingRegistration\Observers;

abstract class Register extends Observer
{
    /**#@+
     * Codes of exceptions related to customer model
     */
    const EXCEPTION_EMAIL_NOT_CONFIRMED       = 1;
    const EXCEPTION_INVALID_EMAIL_OR_PASSWORD = 2;
    const EXCEPTION_EMAIL_EXISTS              = 3;
    const EXCEPTION_INVALID_RESET_PASSWORD_LINK_TOKEN = 4;

    protected $showSuccessPendingMessage = false;

    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new \Zend_Filter_LocalizedToNormalized([
            'date_format' => $this->getLocalDate()->getDateFormat(\IntlDateFormatter::SHORT)
        ]);
        $filterInternal = new \Zend_Filter_NormalizedToLocalized([
            'date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT
        ]);

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }
    public function createCustomerReg(){
        /** @var \Itoris\RegFields\Controller\Account\CreatePost $regFieldsController */
        $regFieldsController = $this->_objectManager->create('Itoris\RegFields\Controller\Account\CreatePost');
        $regFieldsController->execute();
    }
    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new \Magento\Framework\Exception\InputException(__('Please make sure your passwords match.'));
        }
    }

    public function createCustomer($controller = null) {
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = $this->getDataHelper();
        $session = $this->getCustomerSession();
        /** @var \Magento\Framework\App\ActionFlag $flag */
        $flag = $this->_objectManager->get('Magento\Framework\App\ActionFlag');

        try {
            if($flag->get('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH)){
                $session->setCustomerFormData($this->getRequest()->getParams());
                $this->getResponse()->sendHeaders();
                $helper->safeExit($controller);
            }
                if ($session->isLoggedIn()) {
                    return;
                }

                if ($this->getRequest()->isPost()) {
                    $errors = [];
                    /**---------------Create Account--------------------*/
                    $customerData = $this->customerExtractor->extract('customer_account_create', $this->getRequest());
                    $password = $this->getRequest()->getParam('password');
                    $confirmation = $this->getRequest()->getParam('password_confirmation');
                    $redirectUrl = $this->getCustomerSession()->getBeforeAuthUrl();

                    $this->checkPasswordConfirmation($password, $confirmation);
                    /** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
                    $accountManagement = $this->_objectManager->create('Magento\Customer\Api\AccountManagementInterface');
                    $customerData = $accountManagement
                        ->createAccount($customerData, $password, $redirectUrl);

                    if ($this->getRequest()->getParam('is_subscribed', false)) {
                        $this->subscriberFactory->create()->subscribeCustomerById($customerData->getId());
                    }
                    /**----------------------------------------------------*/

                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerData->getId());
                    /*
                    foreach ($this->getConfig()->getFieldset('customer_account') as $code => $node) {
                        if (array_key_exists('create', $node) && ($value = $this->getRequest()->getParam($code)) !== null) {
                            $customer->setData($code, $value);
                        }
                    }

                    if ($this->getRequest()->getParam('is_subscribed', false)) {
                        $customer->setIsSubscribed(1);
                    }
                    */

                    /**
                     * Initialize customer group id
                     */
                    $customer->getGroupId();

                    if ($this->getRequest()->getPost('create_address')) {

                        /** @var \Magento\Customer\Model\Address $address */
                        $address = $this->_objectManager->create('Magento\Customer\Model\Address')
                            ->setData($this->getRequest()->getPostValue())
                            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false))
                            ->setId(null);
                        $customer->addAddress($address);

                        $errors = $address->validate();
                        if (!is_array($errors)) {
                            $errors = [];
                        }
                    }

                    $validationCustomer = $customer->validate();
                    if (is_array($validationCustomer)) {
                        $errors = array_merge($validationCustomer, $errors);
                    }
                    $validationResult = count($errors) == 0;

                    if (true === $validationResult) {
                        $customer->save();
                        $this->getEventManager()->dispatch('customer_register_success',
                            ['account_controller' => $controller, 'customer' => $customer]
                        );

                        if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
                            $customer->sendNewAccountEmail('confirmation', $this->getCustomerSession()->getBeforeAuthUrl());
                            $this->getMessageManager()->addSuccessMessage(__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                                $this->getCustomerUrl()->getEmailConfirmationUrl($customer->getEmail())));
                        } else {
                            $this->showSuccessPendingMessage = true;
                        }
                        return;
                    } else {
                        $session->setCustomerFormData($this->getRequest()->getPostValue());
                        if (is_array($errors)) {
                            foreach ($errors as $errorMessage) {
                                $this->getMessageManager()->addError(__($errorMessage));
                            }
                        } else {
                            $this->getMessageManager()->addError(__('Invalid customer data'));
                        }
                    }
                }

        } catch (\Exception $e) {
            $this->getMessageManager()->addError($e->getMessage());
            $session->setCustomerFormData($this->getRequest()->getParams());
            /** @var \Magento\Framework\App\Response\RedirectInterface $redirect */
            $redirect = $this->_objectManager->create('Magento\Framework\App\Response\RedirectInterface');
            $controller->getResponse()->setRedirect($redirect->getRefererUrl());
            if(($this->getRequest()->getParam('itorisAjaxLogin') || !(int) $this->getRequest()->getParam('itorisAjaxLogin') && $this->_objectManager->create('Itoris\PendingRegistration\Helper\MagentoVersion')->getMagentoVersion()>2.07)){
                $this->getEventManager()->dispatch('itorias_pending_ajax_register');
            }
            $this->getResponse()->sendHeaders();
            $helper->safeExit($controller);
        }
        //$session->setEscapeMessages(true);
    }

    protected function writePendingStatusInDb($email, $scope, \Itoris\PendingRegistration\Helper\Data $helper) {
        /** @var $db \Magento\Framework\DB\Adapter\AdapterInterface|false */
        $db = $this->getResourceConnection()->getConnection();

        $usersTableName = $this->getResourceConnection()->getTableName('itoris_pendingregistration_users');
        $customersTableName = $this->getResourceConnection()->getTableName('customer_entity');
        $websiteId = (int)$this->getStoreManager()->getWebsite()->getId();
        $data = $db->fetchRow('SELECT * FROM '.$customersTableName.' WHERE email=? and website_id='.$websiteId, $email);

        if (isset($data['email'])) {
            $id = intval($data[ 'entity_id' ]);

            $isExists = $db->fetchOne("SELECT COUNT(*) FROM ".$usersTableName." WHERE customer_id=?", $id);
            /** @var $user \Magento\Customer\Model\Customer */
            $user = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($id);
            $status = $user->getConfirmation() && $user->isConfirmationRequired()
                ? \Itoris\PendingRegistration\Model\Users::STATUS_NOT_CONFIRMED_BY_EMAIL
                : \Itoris\PendingRegistration\Model\Users::STATUS_PENDING;
            $groups = $helper->getGroups();
            if (!empty($groups) && !in_array($user->getGroupId(), $groups)) {
                $status = \Itoris\PendingRegistration\Model\Users::STATUS_APPROVED;
            }
            if (!$isExists) {
                $db->query(
                    "INSERT INTO $usersTableName SET customer_id=?, status={$status}, ip=?, date=CURRENT_TIMESTAMP()",
                    [$id, $_SERVER['REMOTE_ADDR']]
                );
                if (!$status) {
                    $helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_ADMIN, $user, $scope);
                    $helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_USER, $user, $scope);
                }
            }
            return $status;
        }
        return null;
    }

    public function addPendingStatusToCustomer($customer) {
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = $this->getDataHelper();
        $helper->init(null);
        $scope = $helper->getFrontendScope();
        if (!\Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope)) {
            return null;
        }
        if (!$helper->isEnabled()) {
            return null;
        }

        $status = $this->writePendingStatusInDb($customer->getEmail(), $scope, $helper);
        if ($status != 1) {
            $customer->setPendingMessage(__('Thank You for creating an account with us on RoyalWholesalecandy.com. Your account is pending approval and will be reviewed within 24 hours. You will receive another email once your account is confirmed. Any questions please contact us at 1-888-261-8277 or Chat with us!'));
        }

        return $customer;
    }
}
