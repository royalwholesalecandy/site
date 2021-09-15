<?php

namespace BoostMyShop\AdvancedStock\Block\Frontend\Product;


class ConfigurableStocks extends Stocks
{
    protected $_configurableProductFactory;
    protected $_warehouseItemFactory;

    protected $_warehouses;
    protected $_attributes;
    protected $_children;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurableProductFactory,
        array $data = []
    ) {

        parent::__construct($context, $registry, $warehouseFactory, $warehouseCollectionFactory, $config, $warehouseItemCollectionFactory, $data);

        $this->_configurableProductFactory = $configurableProductFactory;
        $this->_warehouseItemFactory = $warehouseItemFactory;
    }

    public function getWarehouses()
    {
        if (!$this->_warehouses)
            $this->_warehouses = $this->_warehouseCollectionFactory->create();
        return $this->_warehouses;
    }

    public function getAttributes()
    {
        if (!$this->_attributes)
            $this->_attributes = $this->_configurableProductFactory->create()->getConfigurableAttributes($this->getProduct());
        return $this->_attributes;
    }

    public function getChildren()
    {
        if (!$this->_children)
        {
            $this->_children = $this->_configurableProductFactory->create()->getUsedProductCollection($this->getProduct());
            foreach($this->getAttributes() as $attr)
                $this->_children->addAttributeToSelect($attr->getProductAttribute()->getattribute_code());
        }

        return $this->_children;
    }

    public function getStock($productId, $warehouseId)
    {
        $stockItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
        if ($stockItem)
            return $stockItem->getwi_available_quantity();
        return 0;
    }

}