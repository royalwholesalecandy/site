<?php

namespace BoostMyShop\OrderPreparation\Model;

class OrderPreparation
{
    protected $_inProgressFactory;
    protected $_inProgressCollectionFactory;
    protected $_config;
    protected $_registry;
    protected $_orderItemFactory;
    protected $_logger;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \BoostMyShop\OrderPreparation\Model\InProgressFactory $inProgressFactory,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressCollectionFactory,
        \BoostMyShop\OrderPreparation\Model\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Model\Order\ItemFactory $logger,
        \BoostMyShop\OrderPreparation\Model\Config $config
    ){
        $this->_inProgressFactory = $inProgressFactory;
        $this->_inProgressCollectionFactory = $inProgressCollectionFactory;
        $this->_config = $config;
        $this->_registry = $registry;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_logger = $logger;
    }


    public function getItemsToShip($order, $warehouseId)
    {
        $items = [];

        foreach($order->getAllItems() as $orderItem)
        {
            switch($orderItem->getproduct_type())
            {
                case 'simple':
                case 'virtual':
                case 'grouped':
                case 'downloadable':
                    $parentItem = null;
                    if ($orderItem->getparent_item_id())
                        $parentItem = $this->_orderItemFactory->create()->load($orderItem->getparent_item_id());
                    if(isset($parentItem)){
                        if($parentItem->getproduct_type() == "bundle"){
                            $qtyToShip = $parentItem->getQtyToShip() * $orderItem->getQtyToShip();
                        }
                        else
                            $qtyToShip = $parentItem->getQtyToShip();
                    }else{
                        $qtyToShip = $orderItem->getQtyToShip();
                    }
                    if ($qtyToShip > 0)
                        $items[$orderItem->getId()] = $qtyToShip;
                    break;
                default:
                    //nothing, we dont handle parent items
                    break;
            }
        }

        return $items;
    }

    /**
     * @param $order
     * @param array $orderItems
     * @param $userId
     */
    public function addOrder($order, $orderItems = [], $userId, $warehouseId)
    {

        if (count($orderItems) == 0)
            $orderItems = $this->getItemsToShip($order, $warehouseId);

        if (count($orderItems) == 0)
            throw new \Exception('This order can not be shipped');

        $invoiceId = $this->getExistingInvoiceId($order);

        $obj = $this->_inProgressFactory->create();
        $obj->setip_order_id($order->getId());
        $obj->setip_user_id($userId);
        $obj->setip_warehouse_id($warehouseId);
        $obj->setip_store_id($order->getStoreId());
        $obj->setip_invoice_id($invoiceId);
        $obj->setip_status(\BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW);
        $obj->save();

        //add order item
        foreach($orderItems as $orderItemId => $qty)
            $obj->addProduct($orderItemId, $qty);

        return $obj;
    }

    /**
     * @param $orderId
     */
    public function remove($inProgressId)
    {
        $obj = $this->_inProgressFactory->create()->load($inProgressId);
        $obj->delete();

        return $this;
    }

    /**
     * @return $this
     */
    public function flush()
    {
        $collection = $this->_inProgressCollectionFactory->create()->addFieldToFilter('ip_status', \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED);
        foreach($collection as $item)
        {
            $item->delete();
        }
        return $this;
    }

    public function massCreate($createShipment, $createInvoice, $warehouseId)
    {
        $errors = [];

        $collection = $this->_inProgressCollectionFactory
                            ->create()
                            ->addFieldToFilter('ip_status', \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW)
                            ->addWarehouseFilter($warehouseId)
                            ->addUserFilter($this->_registry->getCurrentOperatorId())
                            ;
        foreach($collection as $item)
        {
            try
            {
                $item->pack($createShipment, $createInvoice);
            }
            catch(\Exception $ex)
            {
                $errors[] = 'Error for order #'.$item->getOrder()->getIncrementId().' : '.$ex->getMessage();
            }

        }

        return $errors;
    }

    public function getExistingInvoiceId($order)
    {
        foreach($order->getInvoiceCollection() as $invoice)
            return $invoice->getId();
    }

}
