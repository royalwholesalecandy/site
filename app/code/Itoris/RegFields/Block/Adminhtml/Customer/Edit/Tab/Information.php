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

namespace Itoris\RegFields\Block\Adminhtml\Customer\Edit\Tab;

class Information extends \Magento\Backend\Block\Widget\Form\Generic //\Magento\Backend\Block\Widget/*\*/ implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Itoris_RegFields::customer/form.phtml';
    /** @var \Magento\Framework\Registry $_coreRegistry */
    protected $_coreRegistry;
    protected $isEnabled;
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $helper = $this->getRegFieldsHelper();
        $this->_coreRegistry = $helper->getObjectManager()->get('Magento\Framework\Registry');
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
        $this->_scopeConfig = $helper->getObjectManager()->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->isEnabled = $helper->isEnabled();
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    public function _construct(){
        parent::_construct();
        if ($this->isEnabled) {
            //$this->buttonList->update('save', 'label', $this->escapeHtml(__('Save Info')));
            $this->initForm();
            //$this->setTemplate('customer/fields.phtml');
        }
    }
    protected function _prepareForm(){
        //$this->initForm();
        parent::_prepareForm();
    }
    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->getRequest()->getParam('id');//$this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');//$this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return $this
     */
    public function initForm() {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->createModel('Magento\Framework\Data\Form');
        //$form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_custom_options');
        $form->setFieldNameSuffix('custom_options');
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->createModel('Magento\Customer\Model\Customer');
        $storeId = $this->getStoreId();
        if($this->getCustomerId() != null){
            $customer->load($this->getCustomerId());
            $storeId = $customer->getStoreId();
        }
        $fieldset = $form->addFieldset('base_fieldset',
            ['legend'=>$this->escapeHtml(__('Additional Registration Information'))]
        );
        $this->_setFieldset([],$fieldset);
        /** @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset */
        $rendererFieldset = $this->createModel('Magento\Backend\Block\Widget\Form\Renderer\Fieldset');
        $fieldset->setRenderer($rendererFieldset);
        $fieldset->getRenderer()->setTemplate('Itoris_RegFields::customer/fields.phtml');
        $storeCode = $this->_storeManager->getStore($storeId)->getCode();
        $enableValueLocal = $this->_scopeConfig->getValue('itoris_regfields/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeCode);
        $enableValueGlobal = $this->_scopeConfig->getValue('itoris_regfields/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null);
        if ($enableValueGlobal == \Itoris\RegFields\Helper\Data::ENABLED || $enableValueLocal == \Itoris\RegFields\Helper\Data::ENABLED ) {
            /** @var $formModel \Itoris\RegFields\Model\Form */
            $formModel = $this->createModel('Itoris\RegFields\Model\Form');
            $sections = $formModel->getFormConfig($storeId);
            $lastSections = $this->_coreRegistry->registry('sections');
            if(!isset($lastSections) || $lastSections == null){
                $this->_coreRegistry->register('sections',$sections);
            }

        }
        /** @var \Magento\Customer\Model\Customer $customerModel */
        //$customerModel = $this->createModel('Magento\Customer\Model\Customer')->load($customer->getId());
        if($this->getCustomerId() != null){
            $form->setValues($customer->getData());
        }
        $this->setForm($form);
        return $this;
    }
    /**
     * @return string
     */
    /*
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }
    */
    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    public function getRegFieldsHelper(){
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
    }

    /**
     * @param string $class
     * @return mixed
     */
    protected function createModel($class){
        $helperRegFields = $this->getRegFieldsHelper();
        return $helperRegFields->getObjectManager()->create($class);
    }


}
