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
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

//app/code/Itoris/PendingRegistration/Controller/Adminhtml/Index/Index.php
namespace Itoris\PendingRegistration\Controller\Adminhtml\Index;

class Index extends \Itoris\PendingRegistration\Controller\Adminhtml\Index
{
    public function execute(){

        $this->_view->loadLayout();

        $switcher = $this->_view->getLayout()->createBlock('Itoris\PendingRegistration\Block\Admin\StoreSwitcher');
        $this->_view->getLayout()->getBlock('left')->append($switcher);

        /** @var $button \Itoris\PendingRegistration\Block\Admin\Dropdown */
        $button = $this->_view->getLayout()->createBlock( 'Itoris\PendingRegistration\Block\Admin\Dropdown' );
        $button->setScope($this->scope);
        /** @var $scopeToggle \Itoris\PendingRegistration\Block\Admin\ScopeToggle\EngineState */
        $scopeToggle = $this->_view->getLayout()->createBlock('Itoris\PendingRegistration\Block\Admin\ScopeToggle\EngineState', 'scopeToggle');
        $scopeToggle->setScope($this->scope);

        /** @var $sett \Itoris\PendingRegistration\Model\Settings */
        $sett = $this->_objectManager->create('Itoris\PendingRegistration\Model\Settings');
        $scopeToggle->setActive(!$sett->recordExists('active', 'name', $this->scope->getTightScope()));
        $button->append($scopeToggle);
        $this->_view->getLayout()->getBlock('left')->append( $button );

        /** @var $tabs \Itoris\PendingRegistration\Block\Admin\Tabs */
        $tabs = $this->_view->getLayout()->createBlock( 'Itoris\PendingRegistration\Block\Admin\Tabs' );
        $tabs->setScope($this->scope);

        $templateType = $this->getRequest()->getParam('template', \Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_ADMIN);
        $tabs->setActiveTemplateType($templateType);
        $tabs->initTabs();
        $this->_view->getLayout()->getBlock('left')->append($tabs);

        if (\Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($this->scope)) {
            /** @var $form \Itoris\PendingRegistration\Block\Admin\MagentoVariables */
            $text = $this->_view->getLayout()->createBlock('Itoris\PendingRegistration\Block\Admin\MagentoVariables');
            $this->_view->getLayout()->getBlock('content')->append($text);
            /** @var $form \Itoris\PendingRegistration\Block\Admin\Container */
            $form = $this->_view->getLayout()->createBlock( 'Itoris\PendingRegistration\Block\Admin\Container' );
            $form->setScope($this->scope);
            $this->_view->getLayout()->getBlock( 'content' )->append( $form );
            /** @var $scopeToggleTemplate \Itoris\PendingRegistration\Block\Admin\ScopeToggle\TemplateFieldset */
            $scopeToggleTemplate = $this->_view->getLayout()->createBlock('Itoris\PendingRegistration\Block\Admin\ScopeToggle\TemplateFieldset', 'scopeToggleTemplate');
            $scopeToggleTemplate->setScope($this->scope);
            $form->getChild('form')->append($scopeToggleTemplate);

            /** @var $template \Itoris\PendingRegistration\Model\Template */
            $template = $this->_objectManager->create('Itoris\PendingRegistration\Model\Template');
            $template->load($templateType, 'type', $this->scope);
            $template->setType($templateType);
            $form->setEmailTemplate($template);

            $scopeToggleTemplate->setActive(!$template->recordExists($templateType, 'type', $this->scope->getTightScope()));

            $this->_view->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->_view->renderLayout();
    }
}