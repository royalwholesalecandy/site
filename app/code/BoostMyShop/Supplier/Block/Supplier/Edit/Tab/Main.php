<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;
    protected $_countryLists;
    protected $_websiteCollectionFactory;

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
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        array $data = []
    ) {
        $this->_LocaleLists = $localeLists;
        $this->_countryLists = $countryCollectionFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;

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

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main')]);

        if ($model->getId()) {
            $baseFieldset->addField('sup_id', 'hidden', ['name' => 'sup_id']);
            $baseFieldset->addField('sup_product_changes', 'hidden', ['name' => 'sup_product_changes']);
        }

        $baseFieldset->addField(
            'sup_name',
            'text',
            [
                'name' => 'sup_name',
                'label' => __('Name'),
                'id' => 'sup_name',
                'title' => __('Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'sup_code',
            'text',
            [
                'name' => 'sup_code',
                'label' => __('Code'),
                'id' => 'sup_code',
                'title' => __('Code'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_contact',
            'text',
            [
                'name' => 'sup_contact',
                'label' => __('Contact'),
                'id' => 'sup_contact',
                'title' => __('Contact'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_email',
            'text',
            [
                'name' => 'sup_email',
                'label' => __('Email'),
                'id' => 'sup_email',
                'title' => __('Email'),
                'class' => 'validate-email',
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_website',
            'text',
            [
                'name' => 'sup_website',
                'label' => __('Website'),
                'id' => 'sup_website',
                'title' => __('Website'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'sup_locale',
            'select',
            [
                'name' => 'sup_locale',
                'label' => __('Locale'),
                'title' => __('Locale'),
                'values' => $this->_LocaleLists->getTranslatedOptionLocales(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'sup_is_active',
            'select',
            [
                'name' => 'sup_is_active',
                'label' => __('Status'),
                'id' => 'sup_is_active',
                'title' => __('Status'),
                'class' => 'input-select',
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
            ]
        );

        $baseFieldset->addField(
            'sup_website_id',
            'select',
            array(
                'name' => 'sup_website_id',
                'label' => __('Website'),
                'options' => $this->_getWebsiteOptions()
            )
        );

        $baseFieldset->addField(
            'sup_notes',
            'textarea',
            [
                'name' => 'sup_notes',
                'label' => __('Notes'),
                'id' => 'sup_notes',
                'title' => __('Notes'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset = $form->addFieldset('address_fieldset', ['legend' => __('Address')]);

        $addressFieldset->addField(
            'sup_street1',
            'text',
            [
                'name' => 'sup_street1',
                'label' => __('Street 1'),
                'id' => 'sup_street1',
                'title' => __('Street 1'),
                'class' => '',
                'required' => false
            ]
        );


        $addressFieldset->addField(
            'sup_street2',
            'text',
            [
                'name' => 'sup_street2',
                'label' => __('Street 2'),
                'id' => 'sup_street2',
                'title' => __('Street 2'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'sup_postcode',
            'text',
            [
                'name' => 'sup_postcode',
                'label' => __('Postcode'),
                'id' => 'sup_postcode',
                'title' => __('Postcode'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'sup_city',
            'text',
            [
                'name' => 'sup_city',
                'label' => __('City'),
                'id' => 'sup_city',
                'title' => __('City'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'sup_state',
            'text',
            [
                'name' => 'sup_state',
                'label' => __('State / Region'),
                'id' => 'sup_state',
                'title' => __('State / Region'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'sup_country',
            'select',
            [
                'name' => 'sup_country',
                'label' => __('Country'),
                'title' => __('Country'),
                'values' => $this->_countryLists->create()->toOptionArray(),
                'class' => 'select'
            ]
        );

        $addressFieldset->addField(
            'sup_telephone',
            'text',
            [
                'name' => 'sup_telephone',
                'label' => __('Phone'),
                'id' => 'sup_telephone',
                'title' => __('Phone'),
                'class' => '',
                'required' => false
            ]
        );

        $addressFieldset->addField(
            'sup_fax',
            'text',
            [
                'name' => 'sup_fax',
                'label' => __('Fax'),
                'id' => 'sup_fax',
                'title' => __('Fax'),
                'class' => '',
                'required' => false
            ]
        );

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getWebsiteOptions()
    {
        $options = [];
        foreach ($this->_websiteCollectionFactory->create() as $item) {
            $options[$item->getId()] = $item->getname();
        }
        return $options;
    }

}
