<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs;

/**
 * Class Add
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Add extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Config
     */
    protected $_config;

    /**
     * Add constructor.
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ){
        parent::__construct($context, $backendHelper, $data);
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_coreRegistry = $registry;
        $this->_config = $config;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('stock_take_products_to_add');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Products To Add'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addFieldToFilter('type_id', 'simple')
            ->joinTable(
                ['w' => 'bms_advancedstock_warehouse_item'],
                'wi_product_id = entity_id',
                [
                    'location' => 'wi_shelf_location',
                    'qty' => 'wi_physical_quantity',
                    'wi_warehouse_id' => 'wi_warehouse_id'
                ]
            )
            ->joinTable(
                ['st' => 'bms_advancedstock_stock_take_item'],
                'stai_sku = sku',
                [
                    'stai_id' => 'stai_id'
                ],
                'stai_stock_take_id = '.$this->getStockTake()->getId(),
                'left'
            )
            ->addFieldToFilter('wi_warehouse_id', $this->getStockTake()->getsta_warehouse_id())
            ->addFieldToFilter('stai_id', ['null' => true]);

        $manufacturerAttribute = $this->_config->getManufacturerAttribute();
        if ($manufacturerAttribute)
            $collection->addAttributeToSelect($this->_config->getManufacturerAttribute());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'renderer' => 'BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Add\Sku'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );

        $this->addColumn('location',
            [
                'header' => __('Location'),
                'index' => 'location'
            ]
        );

        $this->addColumn('qty',
            [
                'header' => __('Qty'),
                'index' => 'qty',
                'type' => 'range'
            ]
        );

        $this->addColumn('websites', ['header' => __('Websites'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer\Website', 'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\Website']);

        $this->addColumn(
            'action',
            [
                'header' => __('Add'),
                'index' => 'entity_id',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Add\Add',
                'filter' => false,
                'sortable' => false
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
            $params = array_merge($params, ['id' => $this->getStockTake()->getId()]);
        }
        return $this->getUrl('advancedstock/stocktake_edit_add/grid', $params);
    }

    public function getStockTake(){

        return $this->_coreRegistry->registry('current_stocktake');

    }

}