<?php


namespace BoostMyShop\AdvancedStock\Block\Sales\Order\View\Tab;


class AdvancedStock extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'Sales/Order/Edit/Tab/AdvancedStock.phtml';

    protected $_esfoiCollectionFactory;
    protected $_warehouseCollection;
    protected $_warehouseItemCollectionFactory;
    protected $_warehouses;
    protected $_items;
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem\CollectionFactory $esfoiCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Collection $warehouseCollection,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    ) {
        $this->_esfoiCollectionFactory = $esfoiCollectionFactory;
        $this->_warehouseCollection = $warehouseCollection;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_config = $config;

        parent::__construct($context, $registry, $adminHelper, $data);
    }


    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    public function getItems()
    {
        if (!$this->_items)
        {
            $this->_items = $this->_esfoiCollectionFactory
                ->create()
                ->joinOrderItem()
                ->joinWarehouseItem()
                ->addOrderFilter($this->getOrder()->getId())
                ->addProductTypeFilter();
        }
        return $this->_items;
    }

    public function getWarehouses()
    {
        if (!$this->_warehouses)
        {
            $this->_warehouses = $this->_warehouseCollection;
        }
        return $this->_warehouses;
    }

    public function getProductUrl($productId)
    {
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $productId]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $productId]);

        return $url;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('advancedstock/order/save');
    }

    public function getWarehouseItems($productId)
    {
        return $this->_warehouseItemCollectionFactory->create()->addProductFilter($productId)->joinWarehouse();
    }


    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Inventory');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Inventory');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
