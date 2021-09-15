<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Column;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_orderProductFactory = null;
    protected $_config = null;

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
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductFactory,
        \Magento\Framework\Registry $coreRegistry,
        \BoostMyShop\Supplier\Model\Config $config,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_orderProductFactory = $orderProductFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;

        parent::__construct($context, $backendHelper, $data);

        $this->setDefaultLimit(50);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderProductsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setTitle(__('Products'));
        $this->setUseAjax(true);
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
        $collection = $this->_orderProductFactory->create();
        $collection->addOrderFilter($this->getOrder()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn('image', ['header' => __('Image'), 'sortable' => false, 'filter' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Image']);
        $this->addColumn('pop_sku', ['header' => __('Sku'), 'index' => 'pop_sku', 'type' => 'text', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Sku']);
        $this->addColumn('pop_supplier_sku', ['header' => __('Supplier Sku'), 'index' => 'pop_supplier_sku', 'type' => 'text', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\SupplierSku']);
        $this->addColumn('pop_name', ['header' => __('Name'), 'index' => 'pop_name', 'type' => 'text']);
        $this->addColumn('pop_qty', ['header' => __('Qty Ordered'), 'index' => 'pop_qty', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Qty', 'align' => 'center']);

        if ($this->_config->getSetting('general/pack_quantity'))
            $this->addColumn('pop_qty_pack', ['header' => __('Pack qty'), 'index' => 'pop_qty_pack', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\PackQty', 'align' => 'center']);

        $this->addColumn('pop_qty_received', ['header' => __('Qty Received'), 'index' => 'pop_qty_received', 'type' => 'number', 'align' => 'center']);
        $this->addColumn('pop_buying_price', ['header' => __('Buying price (%1)', $this->getOrder()->getCurrency()->getCode()), 'index' => 'pop_price', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Price', 'align' => 'center']);

        if ($this->_config->getSetting('order_product/enable_last_buying_price'))
            $this->addColumn('last_buying_price', ['header' => __('Last buying price'), 'filter' => false, 'sortable' => false, 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\LastBuyingPrice']);

        if ($this->_config->getSetting('order_product/enable_discount'))
            $this->addColumn('pop_discount_percent', ['header' => __('Discount %'), 'index' => 'pop_discount_percent', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Discount', 'align' => 'center']);

        $this->addColumn('pop_subtotal', ['header' => __('Subtotal (%1)', $this->getOrder()->getCurrency()->getCode()), 'type' => 'number', 'index' => 'pop_price', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Subtotal', 'align' => 'right']);

        $this->addColumn('pop_tax_rate', ['header' => __('Tax rate %'), 'index' => 'pop_tax_rate', 'type' => 'number', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\TaxRate', 'align' => 'center']);
        if ($this->_config->getSetting('order_product/enable_eta_at_product_level'))
            $this->addColumn('pop_eta', ['header' => __('ETA'), 'index' => 'pop_eta', 'filter' => false, 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Eta', 'align' => 'center']);

        $this->addColumn('stock_details', ['header' => __('Stock details'),'filter' => false, 'sortable' => false, 'type' => 'renderer', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\StockDetails']);
        if ($this->_config->getSetting('order_product/enable_margin'))
            $this->addColumn('pop_margin', ['header' => __('Margins'), 'filter' => false, 'sortable' => false, 'index' => 'pop_discount_percent', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Margin', 'align' => 'center']);

        $this->addColumn('pop_remove', ['header' => __('Remove'), 'filter' => false, 'sortable' => false, 'index' => 'pop_id', 'renderer' => '\BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer\Remove', 'align' => 'center']);

        return parent::_prepareColumns();
    }


    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsGrid', ['po_id' => $this->getOrder()->getId()]);
    }

    public function getRowUrl($item){
        //empty to not get link to #
    }

}
