<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation;

use Magento\Backend\Block\Widget\Grid\Column;

class Tab extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_preparationRegistry = null;
    protected $_config = null;
    protected $_ordersFactory = null;
    protected $_inProgressCollectionFactory = null;
    protected static $_tabId = 1;

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
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $ordersFactory,
        \BoostMyShop\OrderPreparation\Model\ConfigFactory $config,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_ordersFactory = $ordersFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_config = $config;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        parent::__construct($context, $backendHelper, $data);

        $this->setMessageBlockVisibility(false);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_ordersFactory->create();

        $collection->addFieldToFilter('status', ['in' => $this->getAllowedOrderStatuses()]);

        //exclude orders being prepared
        $selectedOrderIds = $this->_inProgressCollectionFactory->create()->getOrderIds($this->_preparationRegistry->getCurrentWarehouseId());
        if (count($selectedOrderIds) > 0)
            $collection->addFieldToFilter('main_table.entity_id', array('nin' => $selectedOrderIds));

        //add filter on warehouse
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $this->addWarehouseFilter($collection, $warehouseId);

        $this->addAdditionnalFilters($collection);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getAllowedOrderStatuses()
    {
        //to override by children
    }

    public function addAdditionnalFilters($collection)
    {
        //to override by children
    }

    public function addWarehouseFilter(&$collection, $warehouseId)
    {
        //not implement in order preparaiton, as order / warehouse logic doesnt exist
        //function rewritten by advanced stock module

        return $this;
    }

        /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('#'), 'index' => 'increment_id']);
        $this->addColumn('created_at', ['header' => __('Date'), 'index' => 'created_at', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime', 'format' => \IntlDateFormatter::FULL]);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status']);
        $this->addColumn('store_id', ['header' => __('Store'), 'index' => 'store_id', 'renderer' => '\Magento\Backend\Block\Widget\Grid\Column\Renderer\Store']);
        $this->addColumn('shipping_name', ['header' => __('Customer'), 'index' => 'shipping_name', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\CustomerName']);
        $this->addColumn('shipping_information', ['header' => __('Shipping method'), 'index' => 'shipping_information']);
        $this->addColumn('products', ['header' => __('Products'), 'index' => 'entity_id', 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\Products', 'filter' => '\BoostMyShop\OrderPreparation\Block\Preparation\Filter\Products']);
        $this->addColumn('action', ['header' => __('Action'), 'index' => 'index_id', 'align' => 'center', 'filter' => false, 'renderer' => '\BoostMyShop\OrderPreparation\Block\Preparation\Renderer\Actions']);

        return parent::_prepareColumns();
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');


        $this->getMassactionBlock()->addItem(
            'create_order',
            [
                'label' => __('Prepare'),
                'url' => $this->getUrl('*/*/massPrepare', ['_current' => true]),
            ]
        );


    }


}
