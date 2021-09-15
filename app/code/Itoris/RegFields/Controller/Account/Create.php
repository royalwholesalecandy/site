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

class Create extends \Itoris\RegFields\Controller\Account
{
    public function execute(){
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->_getSession()->isLoggedIn() || !$this->getRegistration()->isAllowed()) {
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->getPageFactory()->create();
            //$this->_view->loadLayout();
            if ($this->getDataHelper()->isEnabled() && !$this->getDataHelper()->isDisabledForStore()) {
                /** @var \Magento\Customer\Block\Form\Register $registerForm */
                $registerForm =
                    $resultPage->getLayout()->getBlock('customer_form_register');//$this->_view->getLayout()->getBlock('customer_form_register');
                /** @var $formModel \Itoris\RegFields\Model\Form */
                $formModel = $this->_objectManager->create('Itoris\RegFields\Model\Form');
                $websiteId = $this->getStoreManager()->getWebsite()->getId();
                $storeId = $this->getStoreManager()->getStore()->getId();
                $registerForm->setFormConfig($formModel->getFormConfig($storeId));
                $registerForm->setTemplate('Itoris_RegFields::form/register.phtml');
            }
            //$this->_view->renderLayout();
        return $resultPage;
    }
}