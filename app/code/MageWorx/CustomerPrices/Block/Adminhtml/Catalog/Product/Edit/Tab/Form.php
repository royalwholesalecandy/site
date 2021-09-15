<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Block\Adminhtml\Catalog\Product\Edit\Tab;

use MageWorx\CustomerPrices\Helper\Base as HelperBase;
use Magento\Catalog\Model\Product\Type as ProductTypes;

/**
 * Add Customer Prices form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var HelperBase
     */
    protected $helperBase;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param HelperBase $helperBase
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        HelperBase $helperBase,
        array $data = []
    ) {
        $this->helperBase = $helperBase;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Customer prices form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('customerprices_');

        if (!in_array($this->helperBase->getProductType(), $this->helperBase->getAllowedProductTypes())) {
            $fieldset = $form->addFieldset(
                'customerprices_fieldset',
                [
                    'legend' => __(
                        __(
                            'Customer Price extension does not allow to change price for ' .
                            $this->helperBase->getProductType() . ' product'
                        )
                    )
                ]
            );
            $this->setForm($form);

            return parent::_prepareForm();
        }

        $fieldset = $form->addFieldset('customerprices_fieldset', ['legend' => __('Add Customer Prices')]);
        $fieldset->addClass('ignore-validate');

        $buttonHtml = $this->getButtonHtml(
            __('Search Customer'),
            "",
            'action-secondary',
            'action-customer-search'
        );

        $buttonSaveCustomerPrice = $this->getButtonHtml(
            __('Save Customer Price'),
            "",
            'addPrice',
            'add_price_button'
        );

        $fieldset->setHeaderBar($buttonHtml . $buttonSaveCustomerPrice);
        $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id', 'value' => '']);

        $fieldset->addField(
            'customer_email',
            'text',
            [
                'name'     => 'customer_email',
                'label'    => __('Customer Email'),
                'title'    => __('Customer Email'),
                'required' => false,
                'disabled' => true,
                'value'    => '',
            ]
        );

        $fieldset->addField(
            'customer_id',
            'hidden',
            [
                'name' => 'customer_id',
            ]
        );

        $fieldset->addField(
            'price',
            'text',
            [
                'name'               => 'price',
                'label'              => __('Customer Price'),
                'title'              => __('Customer Price'),
                'required'           => false,
                'after_element_html' =>
                    '<div class="note admin__field-note">' . __('Examples') . ':
                     <ul style="margin-left:25px;">
                     <li>' . __('10.99 - replace product price with given value') . '</li>
                     <li>' . __('±10.99 - increase/decrease current price by given value') . '</li>
                     <li>' . __('±15% - increase/decrease current price by given percent') . '</li><ul></div>',
            ]
        );

        $fieldset->addField(
            'special_price',
            'text',
            [
                'name'               => 'special_price',
                'label'              => __('Customer Special Price'),
                'title'              => __('Customer Special Price'),
                'required'           => false,
                'after_element_html' =>
                    '<div class="note admin__field-note">' . __('Examples') . ':
                     <ul style="margin-left:25px;">
                     <li>' . __('10.99 - replace special price with given value') . '</li>
                     <li>' . __('±10.99 - increase/decrease special price by given value') . '</li>
                     <li>' . __('±15% - increase/decrease special price by given percent') . '</li><ul></div>',
            ]
        );

        $idPrefix    = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $this->setForm($form);

        $this->_eventManager->dispatch(
            'mageworx_customerprices_catalog_product_edit_tab_customerprices_form_prepare_form',
            ['form' => $form]
        );

        return parent::_prepareForm();
    }

    /**
     * Retrieve URL to Generate Action
     *
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('mageworx_customerprices/*/addprice');
    }
}
