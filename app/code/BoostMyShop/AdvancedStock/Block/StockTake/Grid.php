<?php namespace BoostMyShop\AdvancedStock\Block\StockTake;

/**
 * Class Grid
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\CollectionFactory
     */
    protected $_stockTakeCollectionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\StockTakeFactory
     */
    protected $_stockTakeFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $_warehouseCollectionFactory;

    protected $_websiteCollectionFactory;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    protected $_userCollectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\StockTakeFactory $stockTakeFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\CollectionFactory $stockTakeCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\StockTakeFactory $stockTakeFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\CollectionFactory $stockTakeCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ){
        parent::__construct($context, $backendHelper, $data);
        $this->_stockTakeCollectionFactory = $stockTakeCollectionFactory;
        $this->_stockTakeFactory = $stockTakeFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_userCollectionFactory = $userCollectionFactory;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('stockTakeGrid');
        $this->setDefaultSort('sta_created_at');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Stock Takes'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_stockTakeCollectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sta_created_at', ['header' => __('Date'), 'index' => 'sta_created_at', 'type' => 'datetime']);
        $this->addColumn('sta_name', ['header' => __('Label'), 'index' => 'sta_name']);
        $this->addColumn('sta_manager_id',
            [
                'header' => __('Manager'),
                'index' => 'sta_manager_id',
                'type' => 'options',
                'options' => $this->_getManagerAsOptions()
            ]
        );
        $this->addColumn('sta_warehouse_id',
            [
                'header' => __('Warehouse'),
                'index' => 'sta_warehouse_id',
                'type' => 'options',
                'options' => $this->getWarehouseOptions()
            ]
        );
        $this->addColumn('sta_status',
            [
                'header' => __('Status'),
                'index' => 'sta_status',
                'type' => 'options',
                'options' => $this->_stockTakeFactory->create()->getStatuses()
            ]
        );
        $this->addColumn('percent',
            [
                'header' => __('Progress %'),
                'index' => 'sta_progress',
                'type' => 'range'
            ]
        );
        $this->addColumn('sta_website',
            [
                'header' => __('Website'),
                'index' => 'sta_website',
                'type' => 'options',
                'options' => $this->getWebsiteAsOptions()
            ]
        );

        $this->_eventManager->dispatch('bms_advancedstock_stocktake_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }

    /**
     * @return array $options
     */
    public function getWarehouseOptions()
    {
        $options = [];
        foreach($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item)
        {
            $options[$item->getId()] = $item->getw_name();
        }
        return $options;
    }

    protected function getWebsiteAsOptions()
    {
        $options = [];
        foreach($this->_websiteCollectionFactory->create() as $item)
        {
            $options[$item->getId()] = $item->getName();
        }
        return $options;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string
     */
    public function getRowUrl($item){

        return $this->getUrl('advancedstock/stocktake/edit', ['id' => $item->getsta_id()]);

    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    /**
     * @return array $managers
     */
    protected function _getManagerAsOptions(){

        $managers = [];

        foreach($this->_userCollectionFactory->create() as $user){

            $managers[$user->getId()] = $user->getfirstname().' '.$user->getlastname();

        }

        return $managers;

    }

}