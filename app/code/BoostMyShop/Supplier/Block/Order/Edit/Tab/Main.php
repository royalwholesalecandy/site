<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_supplierList = null;
    protected $_warehouseList = null;
    protected $_systemStore;
    protected $_statusList = null;
    protected $_helperUser = null;
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
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Collection $supplierList,
        \BoostMyShop\Supplier\Model\Source\Warehouse $warehouseList,
        \Magento\Store\Model\System\Store $systemStore,
        \BoostMyShop\Supplier\Model\Order\Status $statusList,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \BoostMyShop\Supplier\Helper\User $helperUser,
        array $data = []
    ) {
        $this->_statusList = $statusList;
        $this->_supplierList = $supplierList;
        $this->_warehouseList = $warehouseList;
        $this->_systemStore = $systemStore;
        $this->_helperUser = $helperUser;
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
        $model = $this->_coreRegistry->registry('current_purchase_order');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('po_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Main')]);

        if ($model->getId()) {
            $baseFieldset->addField('po_id', 'hidden', ['name' => 'po_id']);
            $baseFieldset->addField('products_to_add', 'hidden', ['name' => 'products_to_add']);
        }

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $baseFieldset->addField(
            'po_created_at',
            'label',
            [
                'name' => 'po_created_at',
                'label' => __('Created at'),
                'id' => 'po_created_at',
                'title' => __('Created at')
            ]
        );

        $baseFieldset->addField(
            'current_tab',
            'hidden',
            [
                'name' => 'current_tab',
                'label' => __('current_tab'),
                'id' => 'current_tab',
                'title' => __('current_tab')
            ]
        );

        $baseFieldset->addField(
            'po_updated_at',
            'label',
            [
                'name' => 'po_updated_at',
                'label' => __('Updated at'),
                'id' => 'po_updated_at',
                'title' => __('Updated at')
            ]
        );

        $baseFieldset->addField(
            'po_sup_id',
            'select',
            [
                'name' => 'po_sup_id',
                'label' => __('Supplier'),
                'id' => 'po_sup_id',
                'title' => __('Supplier'),
                'values' => $this->_supplierList->toOptionArray(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'po_status',
            'select',
            [
                'name' => 'po_status',
                'label' => __('Status'),
                'id' => 'po_status',
                'title' => __('Status'),
                'values' => $this->_statusList->toOptionArray(),
                'class' => 'select',
                'note'   => __('Use expected status to update supply needs and products in transit. Expected status informs that this PO will be received.')
            ]
        );

        $baseFieldset->addField(
            'po_manager',
            'select',
            [
                'name' => 'po_manager',
                'label' => __('Manager'),
                'id' => 'po_manager',
                'title' => __('Manager'),
                'values' => $this->getUsers(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'po_reference',
            'text',
            [
                'name' => 'po_reference',
                'label' => __('Reference'),
                'id' => 'po_reference',
                'title' => __('Reference'),
                'required' => true
            ]
        );


        $baseFieldset->addField(
            'po_supplier_reference',
            'text',
            [
                'name' => 'po_supplier_reference',
                'label' => __('Supplier reference'),
                'id' => 'po_supplier_reference',
                'title' => __('Supplier reference')
            ]
        );


        $baseFieldset->addField(
            'po_eta',
            'date',
            [
                'name' => 'po_eta',
                'label' => __('Estimated time of arrival'),
                'id' => 'po_eta',
                'title' => __('Estimated time of arrival'),
                'date_format' => $dateFormat,
                'required' => true
            ]
        );


        $baseFieldset->addField(
            'po_store_id',
            'select',
            [
                'name' => 'po_store_id',
                'label' => __('Store'),
                'title' => __('Store'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, false),
                'class' => 'select',
                'note'  => 'Select store to use custom settings for PDF'
            ]
        );

        $baseFieldset->addField(
            'po_website_id',
            'select',
            array(
                'name' => 'po_website_id',
                'label' => __('Website'),
                'options' => $this->_getWebsiteOptions()
            )
        );

        $baseFieldset->addField(
            'po_warehouse_id',
            'select',
            [
                'name' => 'po_warehouse_id',
                'label' => __('Warehouse for receiving'),
                'title' => __('Warehouse for receiving'),
                'values' => $this->_warehouseList->toOptionArray(),
                'class' => 'select'
            ]
        );

        $baseFieldset->addField(
            'po_private_comments',
            'textarea',
            [
                'name' => 'po_private_comments',
                'label' => __('Private Comments'),
                'id' => 'po_private_comments',
                'title' => __('Private Comments')
            ]
        );

        $baseFieldset->addField(
            'po_public_comments',
            'textarea',
            [
                'name' => 'po_public_comments',
                'label' => __('Public Comments'),
                'id' => 'po_public_comments',
                'title' => __('Public Comments'),
                'note'   => __('Displayed on PDF')
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

    protected function getUsers()
    {
        $users = [];
        $userList =  $this->_helperUser->getAllowedUsersForPurchaseOrders();

        foreach($userList as $user)
        {
            $users[$user->getId()] = $user->getFirstname().' '.$user->getLastname();
        }

        return $users;
    }
}
