<?php

namespace BoostMyShop\OrderPreparation\Block\Shipping;

use Magento\Backend\Block\Widget\Grid\Column;

class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_config = null;
    protected $_collectionFactory = null;
    protected $_preparationRegistry;

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
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $collectionFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->_preparationRegistry = $preparationRegistry;

        parent::__construct($context, $backendHelper, $data);

        //$this->setPagerVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $collection = $this->_collectionFactory->create()->addUserFilter($userId)->addWarehouseFilter($warehouseId);
        $collection->addOrderDetails();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('#'), 'index' => 'increment_id']);
        $this->addColumn('created_at', ['header' => __('Date'), 'index' => 'created_at']);
        $this->addColumn('status', ['header' => __('Order Status'), 'index' => 'status']);
        $this->addColumn('store_id', ['header' => __('Store'), 'index' => 'store_id', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Store']);
        $this->addColumn('shipping_name', ['header' => __('Customer'), 'index' => 'shipping_name']);
        $this->addColumn('products', ['header' => __('Products'), 'index' => 'index_id', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\InProgressProducts']);
        $this->addColumn('ip_status', ['header' => __('Preparation Status'), 'index' => 'ip_status']);
        $this->addColumn('ip_shipment_id', ['header' => __('Shipment'), 'index' => 'ip_shipment_id', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Shipping\Renderer\Shipment']);
        $this->addColumn('shipping_information', ['header' => __('Shipping method'), 'index' => 'shipping_information']);
        $this->addColumn('tracking', ['header' => __('Tracking #'), 'align' => 'center', 'index' => 'tracking', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Shipping\Renderer\Tracking']);

        return parent::_prepareColumns();
    }

}
