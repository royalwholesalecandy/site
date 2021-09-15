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

class Confirmation extends \Itoris\RegFields\Controller\Account
{
    public function execute(){

            $email = $this->getRequest()->getPost('email');
            if ((!$this->getDataHelper()->isEnabled() || !$email) && !$this->getDataHelper()->isDisabledForStore()) {
                $this->_objectManager->create('Magento\Customer\Controller\Account\Confirmation')->execute();
                //return parent::confirmationAction();
            }
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            if ($this->_getSession()->isLoggedIn()) {
                $this->_redirect('*/*/');
                return;
            }
            if ($email) {
                try {
                    $customer->setWebsiteId($this->getStoreManager()->getStore()->getWebsiteId())->loadByEmail($email);
                    if (!$customer->getId()) {
                        throw new \Exception('');
                    }
                    if ($customer->getConfirmation()) {
                        $this->_objectManager->create('Itoris\RegFields\Model\Customer')->addOptionsToCustomer($customer);
                        $customer->sendNewAccountEmail('confirmation', '', $this->getStoreManager()->getStore()->getId());
                        $this->messageManager->addSuccess(__('Please, check your email for confirmation key.'));
                    } else {
                        $this->messageManager->addSuccess(__('This email does not require confirmation.'));
                    }
                    $this->_getSession()->setUsername($email);
                    $this->_redirect($this->_url->getUrl('*/*/index', ['_secure' => true]));
                }
                catch (\Exception $e) {
                    $this->messageManager->addError(__('Wrong email.'));
                    $this->_redirect($this->_url->getUrl('*/*/*', ['email' => $email, '_secure' => true]));
                }
                return;
            }

            // output form
            $this->_view->loadLayout();

            $this->_view->getLayout()->getBlock('accountConfirmation')
                ->setEmail($this->getRequest()->getParam('email', $email));

            //$this->_initLayoutMessages('customer/session');
            $this->_view->renderLayout();


    }
}