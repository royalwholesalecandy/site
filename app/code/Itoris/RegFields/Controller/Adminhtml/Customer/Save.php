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

//app/code/Itoris/RegFields/Controller/Adminhtml/Customer/Save.php
namespace Itoris\RegFields\Controller\Adminhtml\Customer;

class Save extends \Itoris\RegFields\Controller\Adminhtml\Form
{
    /** @var \Magento\Framework\Registry  */
    protected $_coreRegister;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ){
        $this->_coreRegister = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Itoris\RegFields\Helper\Data $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
        $data = (array)$this->getRequest()->getPostValue();
        if ($data) {
            $customerId = (int)$this->getRequest()->getParam('id');
            /* @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $storeId = (int)$this->getRequest()->getParam('store');
            if((int)$this->getRequest()->getParam('id') != 0){
                $customer->load($customerId);
                $storeId = $customer->getStoreId();
            }
            $errors = true;

            if ($helper->isEnabled()) {
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
                $formValidation = $helper->getFieldHelper()->validate($params, $formConfig, true);
                if (!empty($formValidation)) {
                    if ($errors === true) {
                        $errors = [];
                    }
                    $errors = array_merge($formValidation, $errors);
                }
            }
            if ($helper->isEnabled() && $errors === true) {
                /** @var $customerOptions \Itoris\RegFields\Model\Customer */
                $customerOptions = $this->_objectManager->create('Itoris\RegFields\Model\Customer');
                //if($customerId != 0){
                //    $customerOptions->saveOptions($params, $customerId);
                //    $this->_session->setItorisParams(false);
                //}
                //else{
                    $this->_session->setItorisParams($params);
                //}

            }elseif(count($errors) != 0){
                foreach($errors as $error){
                    $this->getMessageManager()->addError(__($error));
                }
            }

        }
    }    
    
    protected function _isAllowed() {
        return true;
    }
}