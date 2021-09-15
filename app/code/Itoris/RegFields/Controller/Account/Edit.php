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

class Edit extends \Itoris\RegFields\Controller\Account
{
    public function execute(){
            //$this->_view->loadLayout();
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->getPageFactory()->create();
            //$this->_initLayoutMessages('customer/session');
            //$this->_initLayoutMessages('catalog/session');

            $block = $resultPage->getLayout()->getBlock('customer_edit');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            $data = (array)$this->_getSession()->getCustomerFormData(true);
            $customer = $this->_getSession()->getCustomer();
            if (!empty($data)) {
                $customer->addData($data);
            }
            if ($this->getRequest()->getParam('changepass')==1){
                $customer->setChangePassword(1);
            }

            if ($this->getDataHelper()->isEnabled() && !$this->getDataHelper()->isDisabledForStore()) {
                $store = $customer->getStore();
                $storeId = $store->getId();
                $editBlock = $resultPage->getLayout()->getBlock('customer_edit');
                if ($this->getDataHelper()->isEnabled() && !$this->getDataHelper()->isDisabledForStore()) {
                    $editBlock->setTemplate('Itoris_RegFields::edit.phtml');
                    /** @var $formModel \Itoris\RegFields\Model\Form */
                    $formModel = $this->_objectManager->create('Itoris\RegFields\Model\Form');
                    $editBlock->setFormConfig($formModel->getFormConfig($storeId));
                }
            }

            $resultPage->getConfig()->getTitle()->set(__('Account Information'));
            $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
            $this->_view->renderLayout();

    }
}