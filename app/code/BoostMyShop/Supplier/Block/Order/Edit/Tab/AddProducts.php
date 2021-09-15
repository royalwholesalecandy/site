<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class AddProducts extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_productFactory = null;
    protected $_orderProductCollectionFactory = null;
    protected $_config = null;
    protected $_bmsHelper = null;
    protected $_productSupplierAllFactory = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory
     * @param \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \BoostMyShop\Supplier\Model\Config $config
     * @param array $data
     * @internal param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\ProductSupplier\AllFactory $productSupplierAllFactory,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\Supplier\Model\Config $config,
        \BoostMyShop\Supplier\Helper\Boostmyshop $bmsHelper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_productFactory = $productFactory;
        $this->_orderProductCollectionFactory = $orderProductCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_bmsHelper = $bmsHelper;
        $this->_productSupplierAllFactory = $productSupplierAllFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Add products to purchase order'));
        $this->setUseAjax(true);
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_productFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToSelect('thumbnail');
        if ($this->_config->getBarcodeAttribute())
            $collection->addAttributeToSelect($this->_config->getBarcodeAttribute());
        $collection->addFieldToFilter('type_id', array('in' => array('simple')));

        if($this->_config->getSetting('order_product/require_product_supplier_association')) {
            $collection->getSelect()
                ->join($this->_productSupplierAllFactory->create()->getTable('bms_supplier_product'), 'sp_product_id = entity_id')
                ->where('sp_sup_id ='.$this->getOrder()->getSupplier()->getId());
        }

        $alreadyAddedProducts = $this->_orderProductCollectionFactory->create()->getAlreadyAddedProductIds($this->getOrder()->getId());

        if (count($alreadyAddedProducts) > 0)
            $collection->addFieldToFilter('entity_id', array('nin' => $alreadyAddedProducts));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'in_products',
            [
                'header' => __('Select'),
                'renderer' => 'BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\Checkbox',
                'filter' => 'BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Filter\Checkbox',
                'index' => 'entity_id',
                'sortable' => false,
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'qty',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Quantity'),
                'renderer' => 'BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\Qty',
                'name' => 'qty',
                'inline_css' => 'qty',
                'filter' => false,
                'align' => 'center',
                'type' => 'input',
                'validate_class' => 'validate-number',
                'index' => 'qty'
            ]
        );

        $this->addColumn('supplyneeds_summary', ['header' => __('Supply needs'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\SupplyNeeds']);



        if($this->_bmsHelper->advancedStockModuleIsInstalled())
            $this->addColumn(
                'stock_details',
                [
                    'header' => __('Stock details'),
                    'filter_index' => 'entity_id',
                    'sortable' => false,
                    'type' => 'renderer',
                    'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails',
                    'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\StockDetails'
                ]
            );
        else
            $this->addColumn(
                'stock_details',
                [
                    'header' => __('Stock details'),
                    'filter' => false,
                    'sortable' => false,
                    'type' => 'renderer',
                    'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\StockDetails'
                ]
            );

        $this->addColumn('image', ['header' => __('Image'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\Image']);

        if ($this->_config->getBarcodeAttribute())
            $this->addColumn('barcode', ['header' => __('Barcode'), 'index' => $this->_config->getBarcodeAttribute(), 'type' => 'text']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'type' => 'text', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\Sku']);
        $this->addColumn('suppliers_sku', ['header' => __('Supplier sku'), 'index' => 'entity_id', 'filter' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Filter\SupplierSku', 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\SupplierSku']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name', 'type' => 'text']);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status', 'type' => 'options', 'options' => [1 => __('Enabled'), 2 => __('Disabled')]]);


        $this->addColumn('websites', ['header' => __('Websites'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Widget\Grid\Column\Renderer\Website', 'filter' => 'BoostMyShop\Supplier\Block\Widget\Grid\Column\Filter\Website']);

        $this->addColumn('suppliers', ['header' => __('Suppliers'), 'index' => 'entity_id', 'sortable' => false, 'filter' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Filter\Suppliers', 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer\Suppliers']);

        $this->_eventManager->dispatch('bms_supplier_order_addproducts_grid_preparecolumns', ['grid' => $this]);

        return parent::_prepareColumns();
    }


    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/addProductsGrid', ['po_id' => $this->getOrder()->getId()]);
    }

}
