<?php namespace BoostMyShop\AdvancedStock\Block\LowStock;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_lowStockCollectionFactory;
    protected $_warehouseCollectionFactory;
    protected $_coreRegistry;
    protected $_config;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\LowStock\CollectionFactory $lowStockCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\LowStock\CollectionFactory $lowStockCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_lowStockCollectionFactory = $lowStockCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
    }

    /**
     * Class constructor
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lowStockGrid');
        $this->setDefaultSort('sm_created_at');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Stock Helper'));
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_lowStockCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('image', ['header' => __('Image'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\AdvancedStock\Block\LowStock\Renderer\Image']);

        if ($this->_config->getBarcodeAttribute()) {
            $this->addColumn($this->_config->getBarcodeAttribute(), ['header' => __('Barcode'), 'index' => $this->_config->getBarcodeAttribute()]);
        }

        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => 'BoostMyShop\AdvancedStock\Block\LowStock\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('wi_warehouse_id', ['header' => __('Warehouse'), 'align' => 'center', 'index' => 'wi_warehouse_id', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);
        $this->addColumn('wi_physical_quantity', ['header' => __('Qty in warehouse'), 'align' => 'center', 'type' => 'number', 'index' => 'wi_physical_quantity']);
        $this->addColumn('wi_quantity_to_ship', ['header' => __('Qty to ship'), 'align' => 'center', 'type' => 'number', 'index' => 'wi_quantity_to_ship']);
        $this->addColumn('wi_available_quantity', ['header' => __('Available Qty'), 'align' => 'center', 'type' => 'number', 'index' => 'wi_available_quantity']);

        for ($i=1;$i<=3;$i++) {
            $this->addColumn('sh_range_'.$i, ['header' => __('Shipped for<br>last %1 weeks', $this->_config->getSetting('stock_level/history_range_'.$i)), 'type' => 'number', 'index' => 'sh_range_'.$i, 'align' => 'center']);
        }
        $this->addColumn('average_per_week', ['header' => __('Avg per week'), 'index' => 'average_per_week', 'type' => 'number', 'align' => 'center']);
        $this->addColumn('run_out', ['header' => __('Run out (days)'), 'index' => 'run_out', 'type' => 'number', 'align' => 'center']);

        $this->addColumn('warning_stock_level', ['header' => __('Warning stock level'), 'filter' => false, 'index' => 'warning_stock_level', 'align' => 'center', 'renderer' => '\BoostMyShop\AdvancedStock\Block\LowStock\Renderer\WarningStockLevel', 'sortable' => false]);
        $this->addColumn('ideal_stock_level', ['header' => __('Ideal stock level'), 'filter' => false, 'index' => 'ideal_stock_level', 'align' => 'center', 'renderer' => '\BoostMyShop\AdvancedStock\Block\LowStock\Renderer\IdealStockLevel', 'sortable' => false]);
        $this->addColumn('qty_to_order', ['header' => __('Qty to order'), 'type' => 'number', 'index' => 'qty_to_order', 'align' => 'center']);

        //$this->addColumn('stock_value', ['header' => __('Stock value'), 'index' => 'stock_value', 'type' => 'number']);

        $this->addExportType('*/*/exportProductsCsv', __('CSV'));

        return parent::_prepareColumns();
    }


    public function getWarehouseOptions()
    {
        $options = [];
        foreach ($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item) {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    public function getRowUrl($item)
    {
        //empty to not get link to #
    }
}
