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

//app/code/Itoris/RegFields/Controller/Adminhtml/Customer/Edit.php
namespace Itoris\RegFields\Controller\Adminhtml\Customer;

class Edit extends \Magento\Customer\Controller\Adminhtml\Index
{
    public function execute(){
        $customerId = $this->initCurrentCustomer();
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        // set entered data if was error when we do save
        $data = $this->_objectManager->create('Magento\Backend\Model\Auth\Session')->getCustomerData(true);

        // restore data from SESSION
        if ($data) {
            $request = clone $this->getRequest();
            $request->setParams($data);

            $hasFormClass = true;

            if (isset($data['account'])) {
                if ($hasFormClass) {
                    /* @var $customerForm \Magento\Customer\Model\Form */

                    $customerForm = $this->_objectManager->create('Magento\Customer\Model\Form');
                    $customerForm->setEntity($customer)
                        ->setFormCode('adminhtml_customer')
                        ->setIsAjaxRequest(true);
                    $formData = $customerForm->extractData($request, 'account');
                    $customerForm->restoreData($formData);
                } else {
                    $customer->addData($data['account']);
                }
            }

            if (isset($data['address']) && is_array($data['address'])) {
                if ($hasFormClass) {
                    /* @var $addressForm \Magento\Customer\Model\Form */

                    $addressForm = $this->_objectManager->create('Magento\Customer\Model\Form');
                    $addressForm->setFormCode('adminhtml_customer_address');

                    foreach (array_keys($data['address']) as $addressId) {
                        if ($addressId == '_template_') {
                            continue;
                        }

                        $address = $customer->getAddressItemById($addressId);
                        if (!$address) {
                            /** @var \Magento\Customer\Model\Address $address */

                            $address = $this->_objectManager->create('Magento\Customer\Model\Address');
                            $customer->addAddress($address);
                        }

                        $formData = $addressForm->setEntity($address)
                            ->extractData($request);
                        $addressForm->restoreData($formData);
                    }
                } else {
                    foreach ($data['address'] as $addressId => $address) {
                        $addressModel = $this->_objectManager->create('Magento\Customer\Model\Address')->setData($address)
                            ->setId($addressId);
                        $customer->addAddress($addressModel);
                    }
                }
            }
        }
        /** @var \Itoris\RegFields\Helper\Data $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
        $layout = $helper->getLayoutFactory()->create();
        // prepare html response
        $html = $layout->createBlock('Itoris\RegFields\Block\Adminhtml\Customer\Edit\Tab\Information')->toHtml();
        $this->getResponse()->setBody($html);
    }
    
    protected function _isAllowed() {
        return true;
    }
}