<?php

namespace BoostMyShop\Supplier\Block\ProductSupplier;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_supplierCollectionFactory;
    protected $_config;
    protected $_eavAttribute;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttribute,
        \BoostMyShop\Supplier\Model\ResourceModel\ProductSupplier\AllFactory $productSupplierCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollectionFactory,
        \BoostMyShop\Supplier\Model\Config $config,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_productSupplierCollectionFactory = $productSupplierCollectionFactory;
        $this->_supplierCollectionFactory = $supplierCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_eavAttribute = $eavAttribute;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productSupplierGrid');
        $this->setTitle(__('Product / Supplier Association'));
        //$this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productSupplierCollectionFactory->create();
        $this->addAdditionnalFilter($collection);

        if ($this->_config->getSetting('product_supplier/restrict_to_associated'))
            $collection->addAssociatedFilter();

        if ($this->_config->getManufacturerAttribute())
            $collection->addAttributeToSelect($this->_config->getManufacturerAttribute());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //used to apply additionnal filters for classes extending this one
    protected function addAdditionnalFilter(&$collection)
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        if ($this->_config->getManufacturerAttribute())
            $this->addColumn('manufacturer', ['header' => __('Manufacturer'), 'type' => 'options', 'options' => $this->getManufacturerOptions(), 'index' => $this->_config->getManufacturerAttribute()]);

        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('supplier', ['header' => __('Supplier'), 'index' => 'sup_id', 'type' => 'options', 'options' => $this->getSupplierOptions()]);
        $this->addColumn('associated', ['header' => __('Associated'), 'align' => 'center', 'sortable' => false, 'index' => 'associated', 'type' => 'options', 'options' => ['' => ' ', 0 => __('No'), 1 => __('Yes')]]);
        $this->addColumn('sp_sku', ['header' => __('Supplier sku'), 'align' => 'center', 'index' => 'sp_sku', 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\SupplierSku']);
        $this->addColumn('sup_currency', ['header' => __('Currency'), 'align' => 'center', 'index' => 'sup_currency']);
        $this->addColumn('sp_price', ['header' => __('Buying price'), 'align' => 'center', 'index' => 'sp_price', 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\Price']);
        $this->addColumn('sp_last_buying_price_base', ['header' => __('Last buying price'), 'align' => 'center', 'index' => 'sp_last_buying_price_base', 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\LastBuyingPrice']);
        $this->addColumn('sp_moq', ['header' => __('MOQ'), 'align' => 'center', 'index' => 'sp_moq', 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\Moq']);

        if ($this->_config->getSetting('general/pack_quantity'))
            $this->addColumn('sp_pack_qty', ['header' => __('Pack qty'), 'align' => 'center', 'index' => 'sp_pack_qty', 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\PackQty']);

        $this->addColumn('sp_primary', ['header' => __('Is Primary'), 'align' => 'center', 'index' => 'sp_primary', 'type' => 'options', 'options' => ['' => ' ', 0 => __('No'), 1 => __('Yes')], 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\Primary']);

        $this->addColumn('action', ['header' => __('Action'),'sortable'=> false, 'filter'=>false, 'align' => 'center','type' => 'action', 'is_system' => true, 'renderer' => '\BoostMyShop\Supplier\Block\ProductSupplier\Renderer\Popup']);

        $this->_eventManager->dispatch('bms_supplier_productsupplier_grid_preparecolumns', ['grid' => $this]);

        $this->addExportType('*/*/ExportCsv', __('CSV'));


        return parent::_prepareColumns();
    }

    protected function getManufacturerOptions()
    {
        $options = [];
        $options[''] = ' ';

        $manufacturerAttr = $this->_eavAttribute->get(\Magento\Catalog\Model\Product::ENTITY, $this->_config->getManufacturerAttribute());
        $allOptions = $manufacturerAttr->getSource()->getAllOptions();
        foreach($allOptions as $option)
        {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }


    public function getSupplierOptions()
    {
        $options = [];
        $options[''] = ' ';
        foreach($this->_supplierCollectionFactory->create()->setOrder('sup_name', 'asc') as $item)
        {
            $options[$item->getId()] = $item->getsup_name();
        }
        return $options;
    }

    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('fake_id');

        $this->getMassactionBlock()->addItem(
            'remove',
            [
                'label' => __('Remove'),
                'url' => $this->getMassActionUrl('massRemoveProducts'),
                'confirm' => __('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'associate',
            [
                'label' => __('Associate to supplier'),
                'url' => $this->getMassActionUrl('MassAssociateProducts')
            ]
        );

        $modes = [];
        $modes[] = ['label' => 'Set stock to 0', 'value' => 'stock_to_0'];
        $modes[] = ['label' => 'Set stock to 999', 'value' => 'stock_to_999'];
        $modes[] = ['label' => 'Remove primary', 'value' => 'remove_primary'];
        $modes[] = ['label' => 'Remove supplier sku', 'value' => 'remove_sku'];
        $modes[] = ['label' => 'Remove buying price', 'value' => 'remove_price'];
        $this->getMassactionBlock()->addItem(
            'edit',
            [
                'label' => __('Mass edit'),
                'url' => $this->getMassActionUrl('MassEdit'),
                'additional' => [
                    'mode' => [
                        'name' => 'mode',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Action'),
                        'values' => $modes,
                    ],
                ]

            ]
        );
    }

    protected function getMassActionUrl($action)
    {
        return $this->getUrl('*/*/'.$action);
    }

    public function getRowClass($row)
    {
        return 'product_supplier_'.$row->getfake_id();
    }

    protected function _prepareMassactionColumn()
    {
        $columnId = 'massaction';
        $massactionColumn = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Grid\Column')
            ->setData(
                [
                    'index' => $this->getMassactionIdField(),
                    'filter_index' => $this->getMassactionIdFilter(),
                    'type' => 'massaction',
                    'name' => $this->getMassactionBlock()->getFormFieldName(),
                    'is_system' => true,
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select',
                    'use_index' => 1,   //this is the code line that allows to get a custom ID used for massaction checkboxes
                ]
            );

        if ($this->getNoFilterMassactionColumn()) {
            $massactionColumn->setData('filter', false);
        }

        $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())->setGrid($this)->setId($columnId);

        $this->getColumnSet()->insert(
            $massactionColumn,
            count($this->getColumnSet()->getColumns()) + 1,
            false,
            $columnId
        );
        return $this;
    }

}
