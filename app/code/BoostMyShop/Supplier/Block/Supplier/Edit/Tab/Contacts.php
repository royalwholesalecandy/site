<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

class Contacts extends \Magento\Backend\Block\Widget\Form\Generic
{

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
        array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);
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

        $salesFieldset = $form->addFieldset('sales_fieldset', ['legend' => __('Sales')]);

        $salesFieldset->addField(
            'sup_sales_contact',
            'text',
            [
                'name' => 'sup_sales_contact',
                'label' => __('Contact'),
                'id' => 'sup_sales_contact',
                'title' => __('Contact')
            ]
        );

        $salesFieldset->addField(
            'sup_sales_email',
            'text',
            [
                'name' => 'sup_sales_email',
                'label' => __('Email'),
                'id' => 'sup_sales_email',
                'title' => __('Email')
            ]
        );

        $salesFieldset->addField(
            'sup_sales_phone',
            'text',
            [
                'name' => 'sup_sales_phone',
                'label' => __('Telephone'),
                'id' => 'sup_sales_phone',
                'title' => __('Telephone')
            ]
        );

        $salesFieldset->addField(
            'sup_sales_notes',
            'textarea',
            [
                'name' => 'sup_sales_notes',
                'label' => __('Notes'),
                'id' => 'sup_sales_notes',
                'title' => __('Notes')
            ]
        );


        $accountingFieldset = $form->addFieldset('accounting_fieldset', ['legend' => __('Accounting')]);

        $accountingFieldset->addField(
            'sup_accounting_contact',
            'text',
            [
                'name' => 'sup_accounting_contact',
                'label' => __('Contact'),
                'id' => 'sup_accounting_contact',
                'title' => __('Contact')
            ]
        );

        $accountingFieldset->addField(
            'sup_accounting_email',
            'text',
            [
                'name' => 'sup_accounting_email',
                'label' => __('Email'),
                'id' => 'sup_accounting_email',
                'title' => __('Email')
            ]
        );

        $accountingFieldset->addField(
            'sup_accounting_phone',
            'text',
            [
                'name' => 'sup_accounting_phone',
                'label' => __('Telephone'),
                'id' => 'sup_accounting_phone',
                'title' => __('Telephone')
            ]
        );

        $accountingFieldset->addField(
            'sup_accounting_notes',
            'textarea',
            [
                'name' => 'sup_accounting_notes',
                'label' => __('Notes'),
                'id' => 'sup_accounting_notes',
                'title' => __('Notes')
            ]
        );


        $aftesaleFieldset = $form->addFieldset('aftersale_fieldset', ['legend' => __('Aftersales')]);

        $aftesaleFieldset->addField(
            'sup_aftersale_contact',
            'text',
            [
                'name' => 'sup_aftersale_contact',
                'label' => __('Contact'),
                'id' => 'sup_aftersale_contact',
                'title' => __('Contact')
            ]
        );

        $aftesaleFieldset->addField(
            'sup_aftersale_email',
            'text',
            [
                'name' => 'sup_aftersale_email',
                'label' => __('Email'),
                'id' => 'sup_aftersale_email',
                'title' => __('Email')
            ]
        );

        $aftesaleFieldset->addField(
            'sup_aftersale_phone',
            'text',
            [
                'name' => 'sup_aftersale_phone',
                'label' => __('Telephone'),
                'id' => 'sup_aftersale_phone',
                'title' => __('Telephone')
            ]
        );

        $aftesaleFieldset->addField(
            'sup_aftersale_notes',
            'textarea',
            [
                'name' => 'sup_aftersale_notes',
                'label' => __('Notes'),
                'id' => 'sup_aftersale_notes',
                'title' => __('Notes')
            ]
        );

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
