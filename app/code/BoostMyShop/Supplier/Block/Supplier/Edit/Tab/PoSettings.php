<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

class PoSettings extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_localeLists;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->_localeLists = $localeLists;
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('current_supplier');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('supplier_');

        $baseFieldset = $form->addFieldset('po_settings_fieldset', ['legend' => __('Settings')]);

        $baseFieldset->addField(
            'sup_minimum_of_order',
            'text',
            [
                'name' => 'sup_minimum_of_order',
                'label' => __('Minimum of order'),
                'id' => 'sup_minimum_of_order',
                'title' => __('Minimum of order'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_carriage_free_amount',
            'text',
            [
                'name' => 'sup_carriage_free_amount',
                'label' => __('Carriage Free Amount'),
                'id' => 'sup_carriage_free_amount',
                'title' => __('Carriage Free Amount'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_global_discount',
            'text',
            [
                'name' => 'sup_global_discount',
                'label' => __('Global discount %'),
                'id' => 'sup_global_discount',
                'title' => __('Global discount %'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_currency',
            'select',
            [
                'name' => 'sup_currency',
                'label' => __('Currency'),
                'title' => __('Currency'),
                'values' => $this->_localeLists->getOptionCurrencies(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'sup_tax_rate',
            'text',
            [
                'name' => 'sup_tax_rate',
                'label' => __('Tax rate %'),
                'id' => 'sup_tax_rate',
                'title' => __('Tax rate %'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_supply_delay',
            'text',
            [
                'name' => 'sup_supply_delay',
                'label' => __('Supply delay'),
                'id' => 'sup_supply_delay',
                'title' => __('Supply delay'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_shipping_delay',
            'text',
            [
                'name' => 'sup_shipping_delay',
                'label' => __('Shipping delay'),
                'id' => 'sup_shipping_delay',
                'title' => __('Shipping delay'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_payment_terms',
            'text',
            [
                'name' => 'sup_payment_terms',
                'label' => __('Payment terms'),
                'id' => 'sup_payment_terms',
                'title' => __('Payment terms'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_shipping_instructions',
            'textarea',
            [
                'name' => 'sup_shipping_instructions',
                'label' => __('Shipping instructions'),
                'id' => 'sup_shipping_instructions',
                'title' => __('Shipping instructions'),
                'required' => false,
                'comments' => __('Printed on the purchase order PDF')
            ]
        );

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
