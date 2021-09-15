<?php

namespace BoostMyShop\OrderPreparation\Block\CarrierTemplate\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_carrierList;
    protected $_templateType;

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
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\ShippingMethod $carrierList,
        \BoostMyShop\OrderPreparation\Model\CarrierTemplate\Type $templateType,
        array $data = []
    ) {
        $this->_carrierList  = $carrierList;
        $this->_templateType  = $templateType;
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

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main')]);

        if ($model->getId()) {
            $baseFieldset->addField('ct_id', 'hidden', ['name' => 'ct_id']);
        }

        $baseFieldset->addField(
            'ct_name',
            'text',
            [
                'name' => 'ct_name',
                'label' => __('Name'),
                'id' => 'ct_name',
                'title' => __('Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'ct_disabled',
            'select',
            [
                'name' => 'ct_disabled',
                'label' => __('Status'),
                'id' => 'ct_disabled',
                'title' => __('Status'),
                'class' => 'input-select',
                'options' => ['0' => __('Active'), '1' => __('Inactive')]   //strange, i agree but 0 = active
            ]
        );

        $baseFieldset->addField(
            'ct_type',
            'select',
            [
                'name' => 'ct_type',
                'label' => __('Type'),
                'id' => 'ct_type',
                'title' => __('Type'),
                'class' => 'input-select',
                'options' => $this->_templateType->toOptionArray()
            ]
        );

        $baseFieldset->addField(
            'ct_shipping_methods',
            'multiselect',
            [
                'name' => 'ct_shipping_methods',
                'label' => __('Associated shipping methods'),
                'id' => 'ct_shipping_methods',
                'title' => __('Associated shipping methods'),
                'required' => false,
                'values'    => $this->_carrierList->toOptionArray()
            ]
        );

        $data = $model->getData();

        if (isset($data['ct_shipping_methods']))
            $data['ct_shipping_methods'] = unserialize($data['ct_shipping_methods']);

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
