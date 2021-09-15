<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs;

/**
 * Class Add
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Add extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    protected $_coreRegistry;

    protected $_config;

    /**
     * Add constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    ){
        parent::__construct($context, $backendHelper, $data);
        $this->_productCollectionFactory= $productCollectionFactory;
        $this->_coreRegistry = $registry;
        $this->_config = $config;

        if($this->_coreRegistry->registry('current_transfer')->getId()){
            $this->setDefaultSort('st_qty');
        }

    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transfer_products_to_add');
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
            ->addAttributeToSelect('name');

        if ($this->_config->getBarcodeAttribute())
            $collection->addAttributeToSelect($this->_config->getBarcodeAttribute());

        if($this->_coreRegistry->registry('current_transfer')->getId()) {
            $collection->getSelect()->joinLeft(
                [
                    'transfer_items' => $collection->getTable('bms_advancedstock_transfer_item')
                ],
                'e.entity_id = transfer_items.st_product_id and st_transfer_id = ' . $this->_coreRegistry->registry('current_transfer')->getId()
            );
        }

        $collection->addFieldToFilter('type_id', ['in' => ['simple']]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'image',
            [
                'header' => __('Thumbnail'),
                'index' => 'entity_id',
                'renderer' => 'BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add\Image',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'renderer' => 'BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add\Sku'
            ]
        );

        if ($this->_config->getBarcodeAttribute())
            $this->addColumn($this->_config->getBarcodeAttribute(), ['header' => __('Barcode'), 'index' => $this->_config->getBarcodeAttribute()]);

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );

        $this->addColumn(
            'source_location',
            [
                'header' => __('Stocks'),
                'index' => 'entity_id',
                'filter_index' => 'entity_id',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add\Stocks',
                'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'st_qty',
            [
                'header' => __('Qty to transfer'),
                'index' => 'transfer_items.st_qty',
                'renderer' => 'BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add\QtyToTransfer',
                'filter' => false,
                'sortable' => true
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
        if($this->_coreRegistry->registry('current_transfer')->getId()){
            $params = array_merge($params, ['id' => $this->_coreRegistry->registry('current_transfer')->getId()]);
        }
        return $this->getUrl('advancedstock/transfer_edit_add/grid', $params);
    }

}