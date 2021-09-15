<?php
/**
 * Copyright © 2015 Megnor. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Megnor\ShopByBrand\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;



class Main extends Generic implements TabInterface
{

 /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Megnor\Banner\Model\Status
     */
	
	 /**
	 * @var \Magento\Cms\Model\Wysiwyg\Config
	 */
    protected $_wysiwygConfig;
	
	 
    protected $_status;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Megnor\ShopByBrand\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
		$this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Brand Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Brand Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_megnor_shopbybrand_items');
		$isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Brand Name'), 'title' => __('Brand Name'), 'required' => true]
        );
        
        $fieldset->addField(
            'sort_order',
            'text',
            ['name' => 'sort_order', 'label' => __('Sort Order'), 'title' => __('Sort Order'), 'required' => false]
        );
        
		//$wysiwygDescriptionConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);
		//	'config' => $wysiwygDescriptionConfig
		
		 $fieldset->addField(
            'description',
            'editor',
            ['name' => 'description', 'label' => __('Brand Description'), 'title' => __('Brand DEscription'), 'required' => false]
        );
		
        $fieldset->addField(
            'url_key',
            'text',
            ['name' => 'url_key', 'label' => __('Url Key'), 'title' => __('Url Key'), 'required' => false]
        );
        
        
        $fieldset->addField(
            'logo',
            'image',
            ['name' => 'logo', 'label' => __('logo'), 'title' => __('logo'), 'required' => false ,  'disabled' => $isElementDisabled]
        );
        
         $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => $this->_status->getOptionArray(),
                 'disabled' => $isElementDisabled
            ]
        );
        
         $fieldset->addField(
            'featured',
            'select',
            [
                'label' => __('Featured'),
                'title' => __('Featured'),
                'name' => 'featured',
                'required' => true,
                'options' => $this->_status->getOptionArray(),
                 'disabled' => $isElementDisabled
            ]
        );
        
        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
