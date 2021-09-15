<?php

namespace BoostMyShop\Supplier\Model\Order;


class Reception extends \Magento\Framework\Model\AbstractModel
{
    protected $_date;
    protected $_receptionItemFactory;
    protected $_orderProductFactory;
    protected $_orderReceptionItemCollectionFactory;
    protected $_order;
    protected $_orderFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\ResourceModel\Order\Reception');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \BoostMyShop\Supplier\Model\Order\Reception\ItemFactory $receptionItemFactory,
        \BoostMyShop\Supplier\Model\Order $orderFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\ProductFactory $orderProductFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item\CollectionFactory $orderReceptionItemCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_date = $date;
        $this->_receptionItemFactory = $receptionItemFactory;
        $this->_orderProductFactory = $orderProductFactory;
        $this->_orderFactory = $orderFactory;
        $this->_orderReceptionItemCollectionFactory = $orderReceptionItemCollectionFactory;
    }

    public function beforeSave()
    {
        parent::beforeSave();
        $this->setpor_updated_at($this->_date->gmtDate());
        if (!$this->getId())
            $this->setpor_created_at($this->_date->gmtDate());
        return $this;
    }

    public function init($userName, $order)
    {
        $this->setpor_po_id($order->getId());
        $this->setpor_username($userName);
    }

    public function getOrder()
    {
        if (!$this->_order)
        {
            $this->_order = $this->_orderFactory->load($this->getpor_po_id());
        }
        return $this->_order;
    }

    public function addProducts($products)
    {

        foreach($products as $productId => $data)
            $this->addProduct($productId, $data['qty'], (isset($data['qty_pack']) ? $data['qty_pack'] : 1));

        $this->updateReceivedQty();
        $this->getOrder()->updateDeliveryProgress();
    }

    public function addProduct($productId, $qty, $qtyPack = 1)
    {
        if ($qty == 0)
            return $this;

        $obj = $this->_receptionItemFactory->create();
        $obj->setpori_por_id($this->getId());
        $obj->setpori_product_id($productId);
        $obj->setpori_qty($qty);
        $obj->setpori_qty_pack($qtyPack);
        $obj->setpori_condition('good');
        $obj->save();

        //update received quantity for order product record
        $orderId = $this->getpor_po_id();
        $this->_orderProductFactory->create()->updateReceivedQuantity($orderId, $productId);

        return $this;
    }

    public function getAllItems()
    {
        return $this->_orderReceptionItemCollectionFactory->create()->addReceptionFilter($this->getId())->addOrderProductDetails();
    }

    public function updateReceivedQty()
    {
        $this->_getResource()->updateReceivedQty($this->getId());
    }
}
