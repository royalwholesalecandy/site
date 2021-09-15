<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class Import extends \Magento\Backend\Block\Widget\Form\Generic
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
        $model = $this->_coreRegistry->registry('current_carrier_template');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('template_');

        $baseFieldset = $form->addFieldset('import_fieldset', ['legend' => __('Tracking file import')]);

        $baseFieldset->addField(
            'ct_import_file_skip_first_line',
            'select',
            [
                'name' => 'ct_import_file_skip_first_line',
                'label' => __('Skip first line'),
                'id' => 'ct_import_file_skip_first_line',
                'title' => __('Skip first line'),
                'class' => 'input-select',
                'options' => ['0' => __('No'), '1' => __('Yes')]
            ]
        );

        $baseFieldset->addField(
            'ct_import_file_separator',
            'select',
            [
                'name' => 'ct_import_file_separator',
                'label' => __('Field separator'),
                'id' => 'ct_import_file_separator',
                'title' => __('Field separator'),
                'class' => 'input-select',
                'options' => [',' => __('Coma'), ';' => __('Semicolon'), '  ' => __('Tabulation')]
            ]
        );

        $baseFieldset->addField(
            'ct_import_file_shipment_reference_index',
            'text',
            [
                'name' => 'ct_import_file_shipment_reference_index',
                'label' => __('Shipment reference column index (from 1)'),
                'id' => 'ct_import_file_shipment_reference_index',
                'title' => __('Shipment reference column index (from 1)')
            ]
        );

        $baseFieldset->addField(
            'ct_import_file_tracking_index',
            'text',
            [
                'name' => 'ct_import_file_tracking_index',
                'label' => __('Tracking number column index (from 1)'),
                'id' => 'ct_import_file_tracking_index',
                'title' => __('Tracking number column index (from 1)')
            ]
        );

        $data = $model->getData();


        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getMimeTypes()
    {
        $types = [];

        $types['text/plain'] = 'text, csv';
        $types['application/pdf'] = 'pdf';
        $types['text/xml'] = 'xml';

        return $types;
    }

}
