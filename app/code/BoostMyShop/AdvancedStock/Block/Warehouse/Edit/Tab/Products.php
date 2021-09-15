<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_productsFactory = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

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
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Product\AllFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);

        $this->_productsFactory = $productFactory;
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
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Products'));
        $this->setUseAjax(true);
    }

    protected function getWarehouse()
    {
        return $this->_coreRegistry->registry('current_warehouse');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productsFactory->create();
        $collection->addWarehouseFilter($this->getWarehouse()->getId());
        $collection->addRowValue();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Renderer\Sku']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        $this->addColumn('wi_physical_quantity', ['header' => __('Qty in warehouse'), 'type' => 'number', 'index' => 'wi_physical_quantity']);
        $this->addColumn('wi_available_quantity', ['header' => __('Qty available'), 'type' => 'number', 'index' => 'wi_available_quantity']);
        $this->addColumn('wi_quantity_to_ship', ['header' => __('Qty to ship'), 'type' => 'number', 'index' => 'wi_quantity_to_ship']);
        $this->addColumn('wi_shelf_location', ['header' => __('Shelf location'), 'index' => 'wi_shelf_location']);

        $this->addColumn('cost', ['header' => __('Cost'), 'index' => 'cost', 'type' => 'price']);
        $this->addColumn('total_row_value', ['header' => __('Total<br>Value'), 'index' => 'total_row_value', 'filter' => false, 'sortable' => false, 'type' => 'price']);


        $this->addExportType('*/*/exportProductsCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsGrid', ['w_id' => $this->getWarehouse()->getId()]);
    }

}
