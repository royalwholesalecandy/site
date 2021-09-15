<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class OrdersToShip extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_pendingOrderCollectionFactory = null;

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
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrderCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);

        $this->_pendingOrderCollectionFactory = $pendingOrderCollectionFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ordersGrid');
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
        $collection = $this->_pendingOrderCollectionFactory->create()->addOrderDetails()->addExtendedDetails();
        $collection->addWarehouseFilter($this->getWarehouse()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('order_date', ['header' => __('Date'), 'index' => 'order_date']);
        $this->addColumn('order_increment_id', ['header' => __('Order #'), 'index' => 'order_increment_id', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab\Renderer\Order']);
        $this->addColumn('qty_to_ship', ['header' => __('Qty to ship'), 'type' => 'number', 'align' => 'center', 'index' => 'qty_to_ship']);
        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ordersGrid', ['w_id' => $this->getWarehouse()->getId()]);
    }

}
