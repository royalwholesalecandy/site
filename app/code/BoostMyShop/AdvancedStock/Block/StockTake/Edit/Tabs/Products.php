<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs;

/**
 * Class Products
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory
     */
    protected $_stockTakeItemCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\StockTake\ItemFactory
     */
    protected $_stockTakeItemFactory;

    /**
     * Products constructor.
     * @param \BoostMyShop\AdvancedStock\Model\StockTake\ItemFactory $stockTakeItemFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory $stockTakeItemCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\StockTake\ItemFactory $stockTakeItemFactory,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory $stockTakeItemCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ){
        parent::__construct($context, $backendHelper, $data);
        $this->_stockTakeItemCollectionFactory = $stockTakeItemCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_stockTakeItemFactory = $stockTakeItemFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('stockTakeItemGrid');
        $this->setDefaultSort('stai_id');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Stock Take Items'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_stockTakeItemCollectionFactory->create()->addFieldToFilter('stai_stock_take_id', $this->getStockTake()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {

        $this->addColumn('stai_sku',
            [
                'header' => __('Sku'),
                'index' => 'stai_sku',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Products\Sku'
            ]
        );
        $this->addColumn('stai_name', ['header' => __('Name'), 'index' => 'stai_name']);
        $this->addColumn('stai_location', ['header' => __('Location'), 'index' => 'stai_location']);
        $this->addColumn('stai_expected_qty', ['header' => __('Expected Qty'), 'index' => 'stai_expected_qty', 'type' => 'range']);
        $this->addColumn('stai_scanned_qty',
            [
                'header' => __('Scanned Qty'),
                'index' => 'stai_scanned_qty',
                'type' => 'range',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Products\ScannedQty'
            ]
        );
        $this->addColumn('stai_status',
            [
                'header' => __('Status'),
                'index' => 'stai_status',
                'type' => 'options',
                'options' => $this->_stockTakeItemFactory->create()->getStatuses()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $params = ['_current' => true];
        if($this->getStockTake()->getId()){
            $params = array_merge($params, ['id', $this->getStockTake()->getId()]);
        }
        return $this->getUrl('advancedstock/stocktake_edit_products/grid', $params);
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\StockTake
     */
    public function getStockTake(){

        return $this->_coreRegistry->registry('current_stocktake');

    }

}