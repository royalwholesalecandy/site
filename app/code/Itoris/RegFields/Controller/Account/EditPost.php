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

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;

class EditPost extends \Itoris\RegFields\Controller\Account
{
    protected $resultPageFactory;
    protected $customerRepository;    
    private $emailNotification;

    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }
    
    public function execute(){
            
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $errors = [];

            if ($this->getRequest()->isPost()) {
                /** @var $customer \Magento\Customer\Model\Customer */
                $customer = $this->_getSession()->getCustomer();
                $store = $customer->getStore();
                $websiteId = $store->getWebsiteId();
                $storeId = $store->getId();
                if ($this->getDataHelper()->isEnabled() && !$this->getDataHelper()->isDisabledForStore()) {
                    $params = $this->getRequest()->getParam('itoris', []);
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
                    $formConfig = $formModel->getFormConfig($storeId);
                    $formValidation = $this->_objectManager->create('Itoris\RegFields\Helper\Field')->validate($params, $formConfig, true);
                    $errors = array_merge($formValidation, $errors);
                }


                /** @var $customerForm \Magento\Customer\Model\Form */
                $customerForm = $this->_objectManager->create('Magento\Customer\Model\Form');
                $customerForm->setFormCode('customer_account_edit')
                    ->setEntity($customer);

                $customerData = $customerForm->extractData($this->getRequest());

                $customerErrors = true;

                $customerErrors = $customerForm->validateData($customerData);

                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {

                    $customerForm->compactData($customerData);


                    // If password change was requested then add it to common validation scheme
                    if ($this->getRequest()->getParam('change_password')) {
                        $currPass = $this->getRequest()->getPost('current_password');
                        $newPass = $this->getRequest()->getPost('password');
                        $confPass = $this->getRequest()->getPost('confirmation');

                        $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                        if ($this->_objectManager->create('Magento\Framework\Stdlib\StringUtils')->strpos($oldPass, ':') !== false) {
                            list($_salt, $salt) = explode(':', $oldPass);
                        } else {
                            $salt = false;
                        }

                        if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                            if (strlen($newPass)) {
                                /**
                                 * Set entered password and its confirmation - they
                                 * will be validated later to match each other and be of right length
                                 */
                                $customer->setPassword($newPass);
                                $customer->setConfirmation($confPass);
                                $customer->setPasswordConfirmation($confPass);
                            } else {
                                $errors[] = __('New password field cannot be empty.');
                            }
                        } else {
                            $errors[] = __('Invalid current password');
                        }
                    }

                    // Validate account and compose list of errors if any
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($errors, $customerErrors);
                    }
                }

                if (!empty($errors)) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPostValue());
                    foreach ($errors as $message) {
                        $this->getMessageManager()->addError(_($message));
                    }
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }

                try {
                    $customer->setConfirmation(null);
                    $customer->setPasswordConfirmation(null);
                    $customer->save();
                    $this->_getSession()->setCustomer($customer);
                    $this->getMessageManager()->addSuccess(__('The account information has been saved.'));

                    if ($this->getDataHelper()->isEnabled() && !$this->getDataHelper()->isDisabledForStore()) {
                        /** @var $customerOptions \Itoris\RegFields\Model\Customer */
                        $customerOptions = $this->_objectManager->create('Itoris\RegFields\Model\Customer');
                        $customerOptions->saveOptions($params, $customer->getId());
                    }

                    $this->getEmailNotification()->credentialsChanged(
                        $this->getCustomerDataObject($customer->getId()),
                        $customer->getEmail(),
                        $this->getRequest()->getParam('change_password')
                    );
                    
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                    
                } catch (\Exception $e) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPostValue());
                    $this->getMessageManager()->addError(__($e->getMessage()));
                } catch (\Exception $e) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPostValue())
                        ->addException($e, __('Cannot save the customer.'));
                }
            }

            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
    }
    
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }
}