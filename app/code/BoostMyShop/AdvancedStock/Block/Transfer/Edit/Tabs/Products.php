<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs;

/**
 * Class Products
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\CollectionFactory
     */
    protected $_transferItemCollectionFactory;

    /**
     * @var \Magento\Framework\Registery
     */
    protected $_coreRegistry;

    protected $_config;

    /**
     * Products constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\CollectionFactory $transferItemCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\CollectionFactory $transferItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);
        $this->_transferItemCollectionFactory = $transferItemCollectionFactory;
        $this->_coreRegistry = $registry;
        $this->_config = $config;

    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsTransferGrid');
        $this->setDefaultSort('st_product_id');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Products'));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_coreRegistry->registry('current_transfer')->getItems();
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
                'index' => 'st_product_id',
                'renderer' => 'BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products\Image',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products\Sku'
            ]
        );

        if ($this->_config->getBarcodeAttribute())
            $this->addColumn($this->_config->getBarcodeAttribute(), ['header' => __('Barcode'), 'index' => $this->_config->getBarcodeAttribute()]);

        $this->addColumn('st_qty', ['header' => __('Qty'), 'index' => 'st_qty', 'type' => 'range']);
        $this->addColumn('st_qty_transfered', ['header' => __('Qty transfered'), 'index' => 'st_qty_transfered', 'type' => 'range']);

        $this->addColumn(
            'source_location',
            [
                'header' => __('Source location'),
                'index' => 'sti_id',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products\SourceLocation',
                'filter' => false,
                'sortable' => false
            ]
        );

        $this->addColumn(
            'target_location',
            [
                'header' => __('Target location'),
                'index' => 'sti_id',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products\TargetLocation',
                'filter' => false,
                'sortable' => false
            ]
        );

        $this->addColumn(
            'stocks',
            [
                'header' => __('Stocks'),
                'index' => 'entity_id',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products\Stocks',
                'filter' => false,
                'sortable' => false
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Remove'),
                'index' => 'sti_id',
                'renderer' => '\BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products\Delete',
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
        if($this->getTransfer()->getId()){
            $params = array_merge($params, ['id', $this->getTransfer()->getId()]);
        }
        return $this->getUrl('advancedstock/transfer_edit_products/grid', $params);
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function getTransfer()
    {
        return $this->_coreRegistry->registry('current_transfer');
    }

}