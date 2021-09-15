<?php

namespace BoostMyShop\Supplier\Block\Invoice\Edit\Tab;

class PaymentForm extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_systemStore;
    protected $invoiceHelper;
    
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
        \BoostMyShop\Supplier\Helper\Invoice $invoiceHelper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->invoiceHelper = $invoiceHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $form->setUseContainer(false);
        $form->setHtmlIdPrefix('bsi_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add payment')]);
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $baseFieldset->addField(
            'bsip_date',
            'date',
            [
                'name' => 'bsip_date',
                'label' => __('Date'),
                'id' => 'bsip_date',
                'title' => __('Date'),
                'date_format' => $dateFormat,
            ]
        );

        $baseFieldset->addField(
            'bsip_method',
            'select',
            [
                'name' => 'bsip_method',
                'label' => __('Method'),
                'id' => 'bsip_method',
                'title' => __('Method'),
                'values' => $this->invoiceHelper->getAllowMethods(),
                'class' => 'select',
            ]
        );

        $baseFieldset->addField(
            'bsip_total',
            'text',
            [
                'name' => 'bsip_total',
                'label' => __('Total'),
                'id' => 'bsip_total',
                'title' => __('Total'),
            ]
        );

        $baseFieldset->addField(
            'bsip_notes',
            'textarea',
            [
                'name' => 'bsip_notes',
                'label' => __('Notes'),
                'id' => 'bsip_notes',
                'title' => __('Notes'),
            ]
        );

        $baseFieldset->addField(
            'bsip_submit',
            'button',
            [
                'name' => 'bsip_submit',
                'label' => false,
                'id' => 'bsip_submit',
                'value' => __('Create'),
                'class' => __('action-default action-secondary')
            ]
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    
}
