<?php

namespace BoostMyShop\Supplier\Block\Replenishment;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_replenishmentCollection;
    protected $_supplierCollection;
    protected $_bmsHelper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Replenishment\CollectionFactory $replenishmentCollection,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollection,
        \BoostMyShop\Supplier\Helper\Boostmyshop $bmsHelper,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_replenishmentCollection = $replenishmentCollection;
        $this->_supplierCollection = $supplierCollection;
        $this->_bmsHelper = $bmsHelper;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('replenishmentGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Supply Needs'));
        $this->setUseAjax(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_replenishmentCollection->create()->init();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('image', ['header' => __('Image'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Replenishment\Renderer\Image']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => 'BoostMyShop\Supplier\Block\Replenishment\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('status', ['header' => __('Enabled'), 'index' => 'status', 'type' => 'options', 'options' => [2 => 'Disabled', 1 => 'Enabled']]);
        $this->addColumn('backorders', ['header' => __('Qty for backorders'), 'type' => 'number', 'index' => 'qty_for_backorder', 'align' => 'center']);
        $this->addColumn('low_stock', ['header' => __('Qty for low stock'), 'type' => 'number', 'index' => 'qty_for_low_stock', 'align' => 'center']);
        $this->addColumn('qty_to_receive', ['header' => __('Qty to receive'), 'filter' => false, 'index' => 'qty_to_receive', 'align' => 'center']);
        $this->addColumn('qty_to_order', ['header' => __('Suggested Qty to order'), 'type' => 'number', 'index' => 'qty_to_order', 'align' => 'center']);
        $this->addColumn('reason', ['header' => __('Status'), 'index' => 'reason', 'align' => 'center', 'type' => 'options', 'options' => $this->getReasonsOptions()]);
        $this->addColumn('qty_for_po', ['header' => __('Qty to order'),'filter' => false, 'sortable' => false, 'align' => 'center', 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Replenishment\Renderer\QtyToOrder']);

        if($this->_bmsHelper->advancedStockModuleIsInstalled())
            $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter_index' => 'entity_id', 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Replenishment\Renderer\StockDetails', 'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails']);
        else
            $this->addColumn('stock_details', ['header' => __('Stock details'), 'filter' => false, 'sortable' => false, 'index' => 'entity_id', 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Replenishment\Renderer\StockDetails']);


        $this->addColumn('supply_shipping', ['header' => __('Supply + shipping delay'), 'filter' => false, 'sortable' => false, 'index' => 'supply_shipping']);
        //$this->addColumn('order_before', ['header' => __('Order before'), 'filter' => false, 'sortable' => false, 'index' => 'order_before']);
        $this->addColumn('suppliers', ['header' => __('Suppliers'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\Supplier\Block\Replenishment\Renderer\Suppliers', 'filter' => 'BoostMyShop\Supplier\Block\Replenishment\Filter\Suppliers']);
        $this->_eventManager->dispatch('bms_supplier_replenishment_grid_preparecolumns', ['grid' => $this]);

        return parent::_prepareColumns();
    }

    public function getReasonsOptions()
    {
        $options = [];

        $options['backorder'] = __('Backorders');
        $options['lowstock'] = __('Low stock');
        $options['waiting_for_reception'] = __('Waiting for reception');
        $options['undefined'] = __('Undefined');

        return $options;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/Grid');
    }

    public function toHtml()
    {
        $html = parent::toHtml();
        $html .= '<script>restoreQuantitiesForPo();</script>';
        return $html;
    }

}
