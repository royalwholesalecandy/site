<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Reception extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_orderReceptionFactory = null;

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
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\CollectionFactory $orderReceptionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_orderReceptionFactory = $orderReceptionFactory;
        $this->_coreRegistry = $coreRegistry;
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
        $this->setId('orderReceptionGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Products'));
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
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
        $collection = $this->_orderReceptionFactory->create();
        $collection->addOrderFilter($this->getOrder()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('por_created_at', ['filter' => false, 'sortable' => true, 'align' => 'center', 'header' => __('Date'), 'index' => 'por_created_at', 'type' => 'datetime']);
        $this->addColumn('por_username', ['filter' => false, 'sortable' => true, 'align' => 'center', 'header' => __('User'), 'index' => 'por_username', 'type' => 'text']);
        $this->addColumn('por_product_count', ['filter' => false, 'sortable' => true, 'align' => 'center', 'header' => __('Products received'), 'index' => 'por_product_count', 'type' => 'text']);
        $this->addColumn('por_details', ['filter' => false, 'sortable' => false, 'align' => 'left', 'header' => __('Details'), 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Renderer\Details']);
        $this->addColumn('action', ['filter' => false, 'sortable' => false, 'align' => 'center', 'header' => __('Print'), 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Renderer\PrintReception']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('supplier/order_reception/grid', ['po_id' => $this->getOrder()->getId()]);
    }

}
