<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_systemStore;
    protected $_supplierList = null;
    protected $_statusList = null;
    protected $_typeList = null;

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
        \Magento\Store\Model\System\Store $systemStore,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Collection $supplierList,
        \BoostMyShop\Supplier\Model\Invoice\Status $statusList,
        \BoostMyShop\Supplier\Model\Invoice\Type $typeList,
        array $data = []
    ) {
        $this->_supplierList = $supplierList;
        $this->_statusList = $statusList;
        $this->_typeList = $typeList;
        $this->_systemStore = $systemStore;

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
        $model = $this->_coreRegistry->registry('current_supplier_invoice');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('bsi_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main')]);
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        if ($model->getId()) {
            $baseFieldset->addField('bsi_id', 'hidden', ['name' => 'bsi_id']);
        }

        $isSupDisabled = ($model->getId() ? true : false);
        $baseFieldset->addField(
            'bsi_sup_id',
            'select',
            [
                'name' => 'bsi_sup_id',
                'label' => __('Supplier'),
                'id' => 'bsi_sup_id',
                'title' => __('Supplier'),
                'values' => $this->_supplierList->toOptionArray(),
                'class' => 'select',
                'disabled' => $isSupDisabled
            ]
        );

        $baseFieldset->addField(
            'bsi_date',
            'date',
            [
                'name' => 'bsi_date',
                'label' => __('Date'),
                'id' => 'bsi_date',
                'title' => __('Date'),
                'date_format' => $dateFormat
            ]
        );

        $baseFieldset->addField(
            'bsi_due_date',
            'date',
            [
                'name' => 'bsi_due_date',
                'label' => __('Due date'),
                'id' => 'bsi_due_date',
                'title' => __('Due date'),
                'date_format' => $dateFormat
            ]
        );

        $baseFieldset->addField(
            'bsi_reference',
            'text',
            [
                'name' => 'bsi_reference',
                'label' => __('Reference'),
                'id' => 'bsi_reference',
                'title' => __('Reference'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'bsi_type',
            'select',
            [
                'name' => 'bsi_type',
                'label' => __('Type'),
                'id' => 'bsi_type',
                'title' => __('Type'),
                'values' => $this->_typeList->toOptionArray(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'bsi_total',
            'text',
            [
                'name' => 'bsi_total',
                'label' => __('Total'),
                'id' => 'bsi_total',
                'title' => __('Total')
            ]
        );

        $baseFieldset->addField(
            'bsi_status',
            'select',
            [
                'name' => 'bsi_status',
                'label' => __('Status'),
                'id' => 'bsi_status',
                'title' => __('Status'),
                'values' => $this->_statusList->toOptionArray(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'bsi_notes',
            'textarea',
            [
                'name' => 'bsi_notes',
                'label' => __('Notes'),
                'id' => 'bsi_notes',
                'title' => __('Notes')
            ]
        );

        if($model->getbsi_attachment_filename() == ''){
            $baseFieldset->addField(
                'bsi_attachment_filename',
                'file',
                [
                    'name' => 'bsi_attachment_filename',
                    'label' => __('Attachment'),
                    'id' => 'bsi_attachment_filename',
                    'title' => __('Attachment')
                ]
            );
        } else {
            $baseFieldset->addType('image','\BoostMyShop\Supplier\Block\Invoice\Form\Renderer\Attachment');
            $baseFieldset->addField(
                'bsi_attachment_filename',
                'image',
                [
                    'name' => 'bsi_attachment_filename',
                    'label' => __('Attachment'),
                    'id' => 'bsi_attachment_filename',
                    'title' => __('Attachment'),
                ]
            );
        }


        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
