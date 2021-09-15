<?php

namespace BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_pendingOrdersCollectionFactory;
    protected $_coreRegistry;
    protected $_config;
    protected $_warehouseCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrdersCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_pendingOrdersCollectionFactory = $pendingOrdersCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('pendingOrdersGrid');
        $this->setDefaultSort('order_date');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Pending orders'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('order_date', ['header' => __('Date'), 'index' => 'order_date']);
        $this->addColumn('order_increment_id', ['header' => __('Order #'), 'index' => 'order_increment_id', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders\Renderer\Order']);
        $this->addColumn('order_status', ['header' => __('Status'), 'index' => 'order_status']);
        $this->addColumn('order_customer_name', ['header' => __('Customer'), 'index' => 'order_customer_name']);
        $this->addColumn('qty_to_ship', ['header' => __('Qty to ship'), 'index' => 'esfoi_qty_to_ship']);
        $this->addColumn('esfoi_qty_reserved', ['header' => __('Qty reserved'), 'index' => 'esfoi_qty_reserved']);
        $this->addColumn('esfoi_warehouse_id', ['header' => __('Shipping warehouse'), 'index' => 'esfoi_warehouse_id', 'type' => 'options', 'options' => $this->getWarehouseOptions()]);

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->_pendingOrdersCollectionFactory->create();
        $collection->addProductFilter($this->getProduct());
        $collection->addOrderDetails();
        $collection->addExtendedDetails();
        $collection->addStatusesFilter($this->_config->getPendingOrderStatuses());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('advancedstock/product/pendingOrdersGrid', ['id' => $this->getProduct()->getId()]);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getWarehouseOptions()
    {
        $options = [];
        foreach($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

}
