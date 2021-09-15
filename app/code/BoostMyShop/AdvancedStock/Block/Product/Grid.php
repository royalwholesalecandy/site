<?php namespace BoostMyShop\AdvancedStock\Block\Product;

use Magento\Backend\Block\Widget\Grid\Column;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_productCollectionFactory;
    protected $_config;
    protected $_bmsHelper = null;
    protected $_eavConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('advancedStockProductsGrid');
        $this->setTitle(__('Products'));
        $this->setSaveParametersInSession(true);
    }


    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToSelect('thumbnail');
        $collection->addAttributeToSelect('cost');

        if ($this->_config->getBarcodeAttribute())
            $collection->addAttributeToSelect($this->_config->getBarcodeAttribute());

        if ($this->_config->getManufacturerAttribute())
            $collection->addAttributeToSelect($this->_config->getManufacturerAttribute());


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('image', ['header' => __('Image'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\AdvancedStock\Block\Product\Renderer\Image']);
        $this->addColumn('id', ['header' => __('ID'), 'index' => 'entity_id', 'type' => 'number']);
        $this->addColumn('type_id', ['header' => __('Type'), 'index' => 'type_id']);
        $this->addColumn('sku', ['header' => __('Sku'), 'index' => 'sku', 'renderer' => '\BoostMyShop\AdvancedStock\Block\Product\Renderer\Sku']);

        if ($this->_config->getBarcodeAttribute())
            $this->addColumn($this->_config->getBarcodeAttribute(), ['header' => __('Barcode'), 'index' => $this->_config->getBarcodeAttribute()]);

        if ($this->_config->getManufacturerAttribute())
            $this->addColumn($this->_config->getManufacturerAttribute(), ['header' => __('Manufacturer'), 'index' => $this->_config->getManufacturerAttribute(), 'type' => 'options', 'options' => $this->getManufacturerOptions()]);


        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);
        $this->addColumn('status', ['header' => __('Status'), 'index' => 'status', 'type' => 'options', 'options' => [1 => 'Enabled', 2 => 'Disabled']]);
        $this->addColumn('cost', ['header' => __('Cost'), 'index' => 'cost']);

        $this->addColumn('websites', ['header' => __('Websites'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Product\Renderer\Website', 'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\Website']);

        $this->addColumn('stocks', ['header' => __('Stocks'), 'index' => 'entity_id', 'sortable' => false, 'align' => 'left', 'renderer' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Renderer\StockDetails', 'filter' => 'BoostMyShop\AdvancedStock\Block\Widget\Grid\Filter\StockDetails']);

        $this->_eventManager->dispatch('bms_advancedstock_product_grid', ['grid' => $this]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    public function getManufacturerOptions()
    {
        $options = array();

        $attribute = $this->_eavConfig->getAttribute('catalog_product', $this->_config->getManufacturerAttribute());
        $attributeOptions = $attribute->getSource()->getAllOptions();

        foreach($attributeOptions as $item)
        {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

}
