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

namespace Itoris\RegFields\Controller;

abstract class Account extends \Magento\Customer\Controller\AbstractAccount
{
    const EXCEPTION_EMAIL_EXISTS = 3;
    protected $addressParams = ['company', 'telephone', 'street', 'city', 'country_id', 'region_id', 'postcode', 'fax'];
    protected $addressDefault = ['city', 'country_id', 'street', 'telephone', 'postcode'];
    protected $defaultRegisterParams = ['prefix', 'suffix', 'dob', 'taxvat', 'gender'];

    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ){
        parent::__construct($context);
    }

    protected function isRequiredDefaultParams(){
        $requireFields = [];
        foreach($this->defaultRegisterParams as $param){
            $configValue = $this->getDataHelper()->getScopeConfig('customer/address/'.$param.'_show');
            if($configValue == 'req'){
                array_push($requireFields, $param);
            }
        }
        return $requireFields;
    }

    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            return false;
        }else{
            return true;
        }
    }

    protected function _canCreateAddress($data) {
        foreach ($this->addressParams as $param) {
            if (isset($data[$param]) && !empty($data[$param])) {
                if (is_array($data[$param])) {
                    foreach ($data[$param] as $value) {
                        if (!empty($value)) {
                            return true;
                        }
                    }
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    protected function prepareRedirectPath() {
        switch ($this->getRequest()->getParam('redirect_after')) {
            case 'checkout':
                return 'checkout/onepage';
            default:
                return null;
        }
    }
    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    public function getDataHelper() {
        return $this->_objectManager->create('Itoris\RegFields\Helper\Data');
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function _getSession(){
        return $this->_objectManager->create('Magento\Customer\Model\Session');
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager(){
        return $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry(){
        return $this->_objectManager->get('Magento\Framework\Registry');
    }

    /**
     * @return \Magento\Framework\Filesystem
     */
    public function getFilesystem(){
        return $this->_objectManager->create('Magento\Framework\Filesystem');
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager(){
        return $this->_objectManager->create('Magento\Framework\Event\ManagerInterface');
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessageManager(){
        return $this->messageManager;
    }

    /**
     * @return \Magento\Customer\Model\Registration
     */
    public function getRegistration(){
        return $this->_objectManager->create('Magento\Customer\Model\Registration');
    }

    /**
     * @return \Magento\Customer\Model\CustomerExtractor
     */
    protected function getCustomerExtract(){
        return $this->_objectManager->create('Magento\Customer\Model\CustomerExtractor');
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    protected function getPageFactory(){
        return $this->_objectManager->create('Magento\Framework\View\Result\PageFactory');
    }

    /**
     * @return \Magento\Customer\Model\CustomerExtractor
     */
    protected function getCustomerExtractor(){
        return $this->_objectManager->create('Magento\Customer\Model\CustomerExtractor');
    }

    /**
     * @return \Magento\Customer\Model\Metadata\FormFactory
     */
    protected function getFormFactory(){
        return $this->_objectManager->create('Magento\Customer\Model\Metadata\FormFactory');
    }
}