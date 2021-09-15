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

namespace Itoris\RegFields\Block\Adminhtml\Customer\Edit;

/**
 * Custom Registration Fields form block
 */
class Tab extends \Magento\Ui\Component\Layout\Tabs\TabWrapper
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, array $data = [])
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return Tab label
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return $this->escapeHtml(__('Custom Registration Fields'));
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        //check ACL
        $auth = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Authorization');
        if (!$auth->isAllowed('Itoris_RegFields::regfields')) return false;
        
        if (/*$this->getCustomerId() &&*/ $this->getRegFieldsHelper()->isEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        if (/*$this->getCustomerId() &&*/ $this->getRegFieldsHelper()->isEnabled()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    /*
    public function getTabClass()
    {
        return '';
    }
    */

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('itorisregfields/customer/edit', ['_current' => true]);
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    /*
    public function isAjaxLoaded()
    {
        return true;
    }
    */
    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);//$this->getRequest()->getParam('id');//
    }

    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    public function getRegFieldsHelper(){
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
    }
}
