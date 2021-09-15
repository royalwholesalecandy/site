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

namespace Itoris\RegFields\Block\Adminhtml\Config\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm() {
        if($this->getStoreId() == '1' || $this->getStoreId() == '0'){
            $useWebsite = false;
        }else{
            $useWebsite = (bool)$this->getStoreId();
        }

        if ($useWebsite) {
            $useDefault = (bool)$this->getWebsiteId();
        } else {
            $useDefault = false;
        }
        $form = $this->_formFactory->create([
            'data' => [
                'id'        => 'edit_form',
                'action'    => $this->getUrl('*/*/save', [ 'website_id' => $this->getWebsiteId(), 'store_id' => $this->getStoreId()]),
                'method'    => 'post'
            ]
        ]);

        $form->setUseDefault($useDefault);
        $form->setUseWebsite($useWebsite);

        /** @var $sectionModel \Itoris\RegFields\Model\Form */
        $sectionModel = $this->getRegFieldsHelper()->getObjectManager()->create('Itoris\RegFields\Model\Form');
        $form->setData('sections', $sectionModel->getSectionsJson($this->getStoreId()));
        $form->setData('default_sections', $sectionModel->getDefaultSectionsJson());

        $configFieldset = $form->addFieldset('config_fieldset', ['legend' => $this->escapeHtml(__('Fields Configuration'))]);
        /** @var \Itoris\RegFields\Block\Form\Fieldset\Renderer $configRenderer */
        $configRenderer = $this->getLayout()->createBlock('Itoris\RegFields\Block\Form\Fieldset\Renderer');
        $configFieldset->setRenderer($configRenderer);
        $use = $sectionModel->getUseDefault() === "0" ? true : false;
        $form->setUseParentValue($use);
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    public function getRegFieldsHelper(){
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
    }
    /**
     * Retrieve store id by store code from the request
     *
     * @return int
     */
    protected function getStoreId() {
        $storeId = (int)$this->getRequest()->getParam('store');
        /*
        if($storeId == 0){
            $storeId = $this->_storeManager->getStore()->getId();
        }
        */
        return $storeId;
    }

    /**
     * Retrieve website id by website code from the request
     *
     * @return int
     */
    protected function getWebsiteId() {
        return $this->_storeManager->getWebsite()->getId();
    }
}