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
 * @package    ITORIS_M2_REGISTRATION_FIELDS_MANAGER
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\RegFields\Controller\Account;

class CreatePost extends \Itoris\RegFields\Controller\Account
{
    const MAGENTO_CAPTCHA_ID = 'user_create';

    public function execute(){
        $this->setRequireDefaultInRequest();
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $session = $this->_getSession();
        if ($session->isLoggedIn() || !$this->getRegistration()->isAllowed()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $password = $this->getRequest()->getParam('password');
        $confirmation = $this->getRequest()->getParam('confirmation');
        //$this->getMessageManager();//->setEscapeMessages(true); // prevent XSS injection in user input
        $redirectPath = false;
        if ($this->getRequest()->isPost()) {
            $errors = [];
            $redirectPath = $this->prepareRedirectPath();

            if (!$customer = $this->getRegistry()->registry('current_customer')) {
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->setId(null);
            }

            /** @var \Magento\Customer\Model\Customer $customerModel */
            $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');//->load($customer->getId());
            if ($this->getDataHelper()->isEnabled()) {
                $params = $this->getRequest()->getParam('itoris', []);
                $requestParams = $this->getRequest()->getParams();
                $this->getRequest()->setParams(array_merge($requestParams, $params));
                /** @var \Magento\Customer\Model\Customer $customerModel */
                $customerModel->addData($params);
                $customParams = $params;
                $customParams['firstname'] = $this->getRequest()->getParam('firstname');
                $customParams['lastname'] = $this->getRequest()->getParam('lastname');
                $customParams['email'] = $this->getRequest()->getParam('email');
                $customParams['is_subscribed'] = $this->getRequest()->getParam('is_subscribed');
                $session->setCustomOptions($customParams);
                $captcha = $this->getRequest()->getParam('captcha');
                if (isset($captcha) ) {
                    foreach ($captcha as $key => $value) {
                        if($key != self::MAGENTO_CAPTCHA_ID){
                            $captchaType = explode('_', $key);
                            /** @var $captchaHelper \Itoris\RegFields\Helper\Captcha */
                            $captchaHelper = $this->_objectManager->create('Itoris\RegFields\Helper\Captcha');

                            if(!$captchaHelper->captchaValidate($value, $captchaType[0])) {
                                $errors[] = __('Incorrect captcha');
                            }
                        }
                    }
                }
                $fileItoris = (array)$this->getRequest()->getFiles('itoris');
                if (!empty($fileItoris)) {
                    foreach ($fileItoris as $key => $fileData) {
                        if ((int)$fileData['size']) {
                            $file = $this->getDataHelper()->uploadFiles('itoris[' . $key . ']');
                            if ($file['error']) {
                                if (!is_array($errors)) $errors = [];
                                $errors[] = $file['error'];
                            } else {
                                $params[$key] = serialize([
                                    'name' => $fileData['name'],
                                    'file' => $file['file'],
                                    'size' => $fileData['size'],
                                    'mime' => $fileData['type']
                                ]);
                            }
                        }
                    }
                }
                /** @var $formModel \Itoris\RegFields\Model\Form */
                $formModel = $this->_objectManager->create('Itoris\RegFields\Model\Form');
                $websiteId = $this->getStoreManager()->getWebsite()->getId();
                $storeId = $this->getStoreManager()->getStore()->getId();
                $formConfig = $formModel->getFormConfig($storeId);
                /** @var \Itoris\RegFields\Helper\Field $formValidation */
                $formValidation = $this->_objectManager->create('Itoris\RegFields\Helper\Field');
                $errors = array_merge($formValidation->validate($params, $formConfig, true), $errors);

            }
            /* @var $customerForm \Magento\Customer\Model\Metadata\FormFactory */
            $customerForm = $this->getFormFactory()->create('customer', 'customer_account_create');
            /**$customerForm->setFormCode('customer_account_create')
            ->setEntity($customerModel);*/
            $customerData = $customerForm->extractData($this->getRequest());
            if (isset($params)){

                $customerData = array_merge($params, $customerData);
            }

            /**
             * Initialize customer group id
             */
            $customerModel->getGroupId();

            $mainAddress = [];
            if ($this->getRequest()->getParam('create_address')) {
                $addressData = $this->getRequest()->getPostValue();
                if (isset($params)) {

                    foreach($this->addressDefault as $value){
                        if(!array_key_exists($value, $params)
                            || $params[$value] == ''
                            || $params[$value][0] == ''
                            || $params[$value] == 'none'
                        ){
                            $params[$value] = '---';
                        }
                    }

                    $addressData = array_merge($addressData, $params);
                }
                if ($this->getRequest()->getParam('default_billing', false)) {
                    if ($this->_canCreateAddress($addressData)) {
                        /** @var \Magento\Customer\Model\Address $billingAddress */
                        $billingAddress = $this->_objectManager->create('Magento\Customer\Model\Address');
                        $billingAddress->setData($addressData)->setIsDefaultBilling(true)->setId(null);
                        $mainAddress[] = $billingAddress;
                        //$customerModel->addAddress($billingAddress);
                        //    $addressErrors = $billingAddress->validate();
                        //    if (is_array($addressErrors)) {
                        //        $errors = array_merge($addressErrors, $errors);
                        //    }
                    }
                }
                if ($this->getRequest()->getParam('default_shipping', false)) {
                    $shippingAddressData = [];
                    foreach ($params as $key => $value) {
                        if (strpos($key, 's_') === 0) {
                            $shippingAddressData[substr($key, 2)] = $value;
                        }
                    }
                    $origShippingData = [];
                    if ($this->_canCreateAddress($shippingAddressData)) {
                        $addressData = $this->getRequest()->getPostValue();

                        foreach($this->addressDefault as $value){
                            if(!array_key_exists($value, $shippingAddressData)
                                || $shippingAddressData[$value] == ''
                                || $shippingAddressData[$value][0] == ''
                                || $shippingAddressData[$value] == 'none'
                            ){
                                $shippingAddressData[$value] = '---';
                            }
                        }
                        $shippingAddressData = array_merge($shippingAddressData, $addressData);
                        /** @var \Magento\Customer\Model\Address $shippingAddress */
                        $shippingAddress = $this->_objectManager->create('Magento\Customer\Model\Address');
                        $shippingAddress->setData($shippingAddressData)->setIsDefaultShipping(true)->setId(null);
                        $mainAddress[] = $shippingAddress;
                    }
                }
            }

            try {
                $customerErrors = true;
                $customerErrors = $customerForm->validateData($customerData);

                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {

                    $customerForm->compactData($customerData);
                    $customerModel->setPassword($this->getRequest()->getParam('password'));
                    $customerModel->setConfirmation($this->getRequest()->getParam('confirmation'));
                    $customerModel->setPasswordConfirmation($this->getRequest()->getParam('confirmation'));
                    $customerModel->setData($customerData);

                    $customerErrors = $customerModel->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }
                $validationResult = count($errors) == 0;
                if (true === $validationResult) {
                    $customer = $customerModel->getDataModel();
                    $redirectUrl = $this->_getSession()->getBeforeAuthUrl();
                    /** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
                    $accountManagement = $this->_objectManager->create('Magento\Customer\Api\AccountManagementInterface');
                    $customer = $accountManagement->createAccount($customer, $password, $redirectUrl);
                    $customerModel->load($customer->getId());
                    foreach($mainAddress as $address){
                        $customerModel->addAddress($address);
                    }
                    $customerModel->save();
                    
                    if ($this->getRequest()->getParam('is_subscribed', false)) {
                        $subscriberFactory = $this->_objectManager->create('Magento\Newsletter\Model\SubscriberFactory');
                        $subscriberFactory->create()->subscribeCustomerById($customerModel->getId());
                    }                    

                    if ($this->getDataHelper()->isEnabled() && !$this->getDataHelper()->isDisabledForStore()) {
                        /** @var $customerOptions \Itoris\RegFields\Model\Customer */
                        $customerOptions = $this->_objectManager->create('Itoris\RegFields\Model\Customer');
                        $customerOptions->saveOptions($params, $customer->getId());

                    }
                    $session->unsetData('custom_options');

                    $this->getEventManager()->dispatch('customer_register_success',
                        ['account_controller' => $this, 'customer' => $customerModel]
                    );

                    $session->setCustomerFormData($this->getRequest()->getPostValue());
                    if ($customerModel->getPendingMessage() && ($this instanceof \Itoris\StoreLoginControl\Controller\CustomRegistration\CreatePost || $this instanceof \Itoris\SmartLogin\CustomRegistrationController)) {
                        return $customerModel;
                    } else {
                        if ($customerModel->isConfirmationRequired()) {
                            /*$customerModel->sendNewAccountEmail(
                                'confirmation',
                                $session->getBeforeAuthUrl(),
                                $this->getStoreManager()->getStore()->getId()
                            );*/
                            $this->messageManager->addSuccess(__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                                $this->_objectManager->create('Magento\Customer\Model\Url')->getEmailConfirmationUrl($customerModel->getEmail())));
                            if ($redirectPath) {
                                $session->setAfterItorisRegForm(true);
                                $resultRedirect->setUrl($this->_redirect->success($redirectPath));
                            } else {
                                $resultRedirect->setUrl($this->_redirect->success($this->_url->getUrl('*/*/index', ['_secure' => true])));
                            }
                        } else {
                            $session->setCustomerAsLoggedIn($customerModel);
                            $session->setCustomerIsLoggedIn(true);
                            //$url = $this->_welcomeCustomer($customerModel);

                            if ($redirectPath) {
                                $session->setAfterItorisRegForm(true);
                                $resultRedirect->setUrl($this->_redirect->success($redirectPath));
                            } else {
                               // $this->messageManager->addSuccess(__('Thank you for registering with %1.', $this->getStoreManager()->getStore()->getFrontendName()));
                                $resultRedirect->setUrl($this->_redirect->success($this->_url->getUrl('*/*/', ['_secure'=>true])));
                            }
                        }
                    }
                    $this->getEventManager()->dispatch('controller_action_postdispatch_customer_account_createpost',
                        ['account_controller' => $this, 'request' => $this->getRequest()]
                    );

                    return $resultRedirect;
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPostValue());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $this->messageManager->addError(__($errorMessage));
                        }
                    } else {
                        $this->messageManager->addError(__('Invalid customer data'));
                    }
                }
            } catch (\Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPostValue());
                if ($e->getCode() === self::EXCEPTION_EMAIL_EXISTS) {
                    $url = $this->_url->getUrl('customer/account/forgotpassword');
                    $message = __('There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.', $url);
                    //$this->getMessageManager();//->setEscapeMessages(false);
                } else {
                    $message = __($e->getMessage());
                }
                $this->messageManager->addError($message);
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $session->setCustomerFormData($this->getRequest()->getPostValue())
                    ->addException($e, __('Cannot save the customer.'));
            }
        }

            if ($redirectPath) {
                $session->setAfterItorisRegForm(true);
                $resultRedirect->setUrl($this->_redirect->success($redirectPath));
            } else {
                $resultRedirect->setUrl($this->_redirect->success($this->_url->getUrl('customer/account/create', ['_secure' => true])));
            }

         if(count($errors)>0 && !($this->getRequest()->getParam('itorisAjaxLogin') || (int) $this->getRequest()->getParam('itorisAjaxLogin'))){

                try{
					$dataHelper = $this->_objectManager->create('Itoris\PendingRegistration\Helper\Data');
				} catch (\Exception $e) {
					$dataHelper = false;
				}
                if($dataHelper) {
                    $dataHelper->init(null);
                    if ($dataHelper->isPendingsActive() ) {
                        $this->_eventManager->dispatch('itoris_regfields_pendigs_error');
                    }
                }
         }
        return $resultRedirect;
    }

    protected function setRequireDefaultInRequest(){
        /* Default Register param */
        $dataNew = $this->getRequest()->getParams();
        foreach($this->isRequiredDefaultParams() as $requiredDefaultParam) {
            if($this->getRequest()->getParam('itoris') != null ){
                if (array_key_exists($requiredDefaultParam, $dataNew['itoris'])) {
                    if ($dataNew['itoris'][$requiredDefaultParam] != '') {
                        $dataNew[$requiredDefaultParam] = $dataNew['itoris'][$requiredDefaultParam];
                    }elseif ($requiredDefaultParam == 'dob') {
                        $dataNew[$requiredDefaultParam] = '0/0/0';
                        $dataNew['itoris'][$requiredDefaultParam] = '0/0/0';
                    } else {
                        $dataNew[$requiredDefaultParam] = '---';
                        $dataNew['itoris'][$requiredDefaultParam] = '---';
                    }
                }elseif ($requiredDefaultParam == 'dob') {
                    $dataNew[$requiredDefaultParam] = '0/0/0';
                    $dataNew['itoris'][$requiredDefaultParam] = '0/0/0';
                } else {
                    $dataNew[$requiredDefaultParam] = '---';
                    $dataNew['itoris'][$requiredDefaultParam] = '---';
                }
            }else {
                if ($requiredDefaultParam == 'dob') {
                    $dataNew[$requiredDefaultParam] = '0/0/0';
                } else {
                    $dataNew[$requiredDefaultParam] = '---';
                }
            }
        }
        $this->getRequest()->setParams($dataNew);
    }
}