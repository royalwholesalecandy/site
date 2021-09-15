<?php
namespace BoostMyShop\Supplier\Block\Product\Edit\Tab;

class Supplier extends \Magento\Backend\Block\Template
{
    protected $_template = 'Product/Edit/Tab/Supplier.phtml';

    protected $_supplierProductFactory;
    protected $_orderProductFactory;
    protected $_supplierFactory;
    protected $_coreRegistry = null;
    protected $_productFactory = null;
    protected $_product = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductFactory,
                                \BoostMyShop\Supplier\Model\Supplier $supplierFactory,
                                \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductFactory,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Framework\Registry $registry,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_orderProductFactory = $orderProductFactory;
        $this->_supplierFactory = $supplierFactory;
        $this->_coreRegistry = $registry;
        $this->_productFactory = $productFactory;
    }

    public function getSuppliers()
    {
        return $this->_supplierProductFactory->create()->getSuppliers($this->getProductId());
    }

    public function getOrders()
    {
        return $this->_orderProductFactory->create()->getOrdersHistory($this->getProductId());
    }

    public function formatPrice($supId, $price)
    {
        return $this->_supplierFactory->load($supId)->getCurrency()->format($price, [], false);
    }

    public function getSupplierUrl($supplierId)
    {
        return $this->getUrl('supplier/supplier/edit', ['sup_id' => $supplierId]);
    }

    public function getOrderUrl($orderId)
    {
        return $this->getUrl('supplier/order/edit', ['po_id' => $orderId]);
    }

    public function getProductId()
    {
        if ($this->getData('product_id'))
            return $this->getData('product_id');
        else
        {
            return $this->_coreRegistry->registry('current_product')->getId();
        }
    }

    public function getProduct()
    {
        if (!$this->_product)
            $this->_product = $this->_productFactory->create()->load($this->getProductId());
        return $this->_product;
    }

}