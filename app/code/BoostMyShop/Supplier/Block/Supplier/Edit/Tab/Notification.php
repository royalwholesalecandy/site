<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

class Notification extends \Magento\Backend\Block\Widget\Form\Generic
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

        $baseFieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Notification settings')]);

        $baseFieldset->addField(
            'sup_enable_notification',
            'select',
            [
                'name' => 'sup_enable_notification',
                'label' => __('Send email notification'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $baseFieldset->addField(
            'sup_attach_pdf',
            'select',
            [
                'name' => 'sup_attach_pdf',
                'label' => __('Attach PDF'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );


        $baseFieldset->addField(
            'sup_attach_file',
            'select',
            [
                'name' => 'sup_attach_file',
                'label' => __('Attach File'),
                'class' => 'input-select',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );


        $fileFieldset = $form->addFieldset('file_fieldset', ['legend' => __('File settings')]);

        $fileFieldset->addField(
            'sup_file_name',
            'text',
            [
                'name'  => 'sup_file_name',
                'label' => __('File name'),
                'required' => false,
                'note'  => __('Filename to download. To insert the purchase order number, insert {reference} code')
            ]
        );



        $fileFieldset->addField(
            'sup_file_header',
            'textarea',
            [
                'name' => 'sup_file_header',
                'label' => __('File header'),
                'required' => false,
                'note' => 'First line of the file'
            ]
        );

        $fileFieldset->addField(
            'sup_file_order_header',
            'textarea',
            [
                'name' => 'sup_file_order_header',
                'label' => __('Order header'),
                'required' => false,
                'note' => 'Use for XML export only'
            ]
        );


        $fileFieldset->addField(
            'sup_file_product',
            'textarea',
            [
                'name' => 'sup_file_product',
                'label' => __('Product line'),
                'required' => false,
                'note' => 'Repeated for every products in the purchase order'
            ]
        );

        $fileFieldset->addField(
            'sup_file_order_footer',
            'textarea',
            [
                'name' => 'sup_file_order_footer',
                'label' => __('Order footer'),
                'required' => false,
                'note' => 'Use for XML export only'
            ]
        );

        $fileFieldset->addField(
            'sup_file_footer',
            'textarea',
            [
                'name' => 'sup_file_footer',
                'label' => __('File footer'),
                'required' => false,
                'note' => 'Use for XML export only'
            ]
        );


        $availableCodesFieldset = $form->addFieldset('available_codes_fieldset', ['legend' => __('Codes available')]);
        $availableCodesFieldset->addType('codes', '\BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Renderer\Codes');

        $availableCodesFieldset->addField(
            'codes',
            'codes',
            [
                'label' => __('Codes'),
            ]
        );

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
