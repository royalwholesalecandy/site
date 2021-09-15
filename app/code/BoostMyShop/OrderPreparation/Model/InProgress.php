<?php

namespace BoostMyShop\OrderPreparation\Model;


class InProgress extends \Magento\Framework\Model\AbstractModel
{
    protected $_storeManager;
    protected $_userFactory;
    protected $_orderFactory;
    protected $_order;
    protected $_inProgressItemCollectionFactory;
    protected $_inProgressItemFactory;
    protected $_shipmentHelperFactory;
    protected $_invoiceHelperFactory;
    protected $_invoiceRepository;
    protected $_shipmentRepository;
    protected $_logger;
    protected $_configFactory;
    protected $_regionFactory;
    protected $_carrierTemplate = false;
    protected $_carrierTemplateHelper;

    const STATUS_NEW = 'new';
    const STATUS_PICKED = 'picked';
    const STATUS_PACKED = 'packed';
    const STATUS_SHIPPED = 'shipped';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\User $userFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\InvoiceRepository $invoiceRepository,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item\CollectionFactory $inProgressItemCollectionFactory,
        \BoostMyShop\OrderPreparation\Helper\CarrierTemplate $carrierTemplateHelper,
        \BoostMyShop\OrderPreparation\Model\InProgress\ItemFactory $inProgressItemFactory,
        \BoostMyShop\OrderPreparation\Model\InProgress\ShipmentFactory $shipmentHelperFactory,
        \BoostMyShop\OrderPreparation\Model\InProgress\InvoiceFactory $invoiceHelperFactory,
        \BoostMyShop\OrderPreparation\Model\Config $_configFactory,
        \BoostMyShop\OrderPreparation\Helper\Logger $logger,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_storeManager = $storeManager;
        $this->_userFactory = $userFactory;
        $this->_orderFactory = $orderFactory;
        $this->_inProgressItemCollectionFactory = $inProgressItemCollectionFactory;
        $this->_inProgressItemFactory = $inProgressItemFactory;
        $this->_shipmentHelperFactory = $shipmentHelperFactory;
        $this->_invoiceHelperFactory = $invoiceHelperFactory;
        $this->_invoiceRepository = $invoiceRepository;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_configFactory = $_configFactory;
        $this->_logger = $logger;
        $this->_regionFactory = $regionFactory;
        $this->_carrierTemplateHelper = $carrierTemplateHelper;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress');
    }

    public function beforeDelete()
    {
        $this->_inProgressItemCollectionFactory->create()->deleteForParent($this->getId());
        return parent::beforeDelete();
    }

    public function getStore()
    {
        return $this->_storeManager->getStore($this->getip_store_id());
    }

    public function getOperatorName()
    {
        return $this->_userFactory->load($this->getip_user_id())->getUsername();
    }

    public function getOrder()
    {
        if (!$this->_order)
        {
            $this->_order = $this->_orderFactory->create()->load($this->getip_order_id());
        }
        return $this->_order;
    }

    public function getCustomerName()
    {
        $customerName = $this->getshipping_name();
        if (!$customerName)
        {
            $customerName = $this->getOrder()->getcustomer_firstname().' '.$this->getOrder()->getcustomer_lastname();
            if (strlen($customerName) < 2)
            {
                $shippingAddress = $this->getOrder()->getShippingAddress();
                if ($shippingAddress)
                    $customerName = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                else
                {
                    $billingAddress = $this->getOrder()->getBillingAddress();
                    $customerName = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                }
            }
        }

        return $customerName;
    }

    public function getShipment()
    {
        if ($this->getip_shipment_id())
        {
            $shipment = $this->_shipmentRepository->get($this->getip_shipment_id());
            $shipment->setOrder($this->getOrder());
            return $shipment;
        }
    }

    public function getInvoice()
    {
        if ($this->getip_invoice_id())
        {
            return $this->_invoiceRepository->get($this->getip_invoice_id());
        }
    }

    public function addProduct($orderItemId, $qty)
    {
        $obj = $this->_inProgressItemFactory->create();
        $obj->setipi_order_id($this->getip_order_id());
        $obj->setipi_order_item_id($orderItemId);
        $obj->setipi_qty($qty);
        $obj->setipi_parent_id($this->getId());
        $obj->save();

        return $obj;
    }

    public function getAllItems()
    {
        return $this->_inProgressItemCollectionFactory
                    ->create()
                    ->addParentFilter($this->getId())

                    ->joinOrderItem();
    }

    public function getLabel()
    {
        return "#".$this->getOrder()->getincrementId()." (".$this->getshipping_name().") - ".__($this->getip_status());
    }

    public function loadByShipmentReference($shipmentReference)
    {
        $id = $this->_getResource()->getIdFromShipmentReference($shipmentReference);
        return $this->load($id);
    }

    public function loadFromOrderIdAndContext($orderId, $warehouseId, $operatorId)
    {
        $id = $this->_getResource()->getIdFromOrderIdAndContext($orderId, $warehouseId, $operatorId);
        return $this->load($id);
    }

    /**
     *
     */
    public function pack($createShipment, $createInvoice, $quantities = null, $totalWeight = null)
    {
        if ($createInvoice)
        {
            if ($this->getOrder()->canInvoice())
            {
                $this->_logger->log('Create invoice for order #'.$this->getOrder()->getIncrementId());

                $invoice = $this->_invoiceHelperFactory->create()->createInvoice($this, $quantities);
                $this->setip_invoice_id($invoice->getId())->save();
            }
        }
        else
            $this->_logger->log('DO NOT Create invoice for order #'.$this->getOrder()->getIncrementId());

        if ($createShipment)
        {
            $this->_logger->log('Create shipment for order #'.$this->getOrder()->getIncrementId());

            $shipment = $this->_shipmentHelperFactory->create()->createShipment($this, $quantities);
            if ($totalWeight)
                $shipment->settotal_weight($totalWeight)->save();
            $this->setip_shipment_id($shipment->getId())->save();

        }
        else
            $this->_logger->log('DO NOT Create shipment for order #'.$this->getOrder()->getIncrementId());

        $this->setip_status(self::STATUS_PACKED)->save();
        $statusFromConfForCompelteOrder = $this->_configFactory->getOrderStateComplete();
        $statusFromConfForUncompleteOrder = $this->_configFactory->getOrderStateProcessing();
        $canChangeOrderStatusAfterPacking = $this->_configFactory->getChangeOrderStatusAfterPacking();
        if($canChangeOrderStatusAfterPacking)
        {
            $orderState = $this->getOrder()->getState();
            if($statusFromConfForCompelteOrder){
                if($orderState == \Magento\Sales\Model\Order::STATE_COMPLETE)            
                    $this->getOrder()->setStatus($statusFromConfForCompelteOrder)->save();
            }
            if($statusFromConfForUncompleteOrder)
            {
                if($orderState == \Magento\Sales\Model\Order::STATE_PROCESSING)            
                    $this->getOrder()->setStatus($statusFromConfForUncompleteOrder)->save();
            }
        }

        //call at the end so tracking # is properly stored
        if ($createShipment && $this->getCarrierTemplate())
            $this->getCarrierTemplate()->afterShipment($this);

        return $this;
    }

    public function addTracking($trackingNumber)
    {
        if (!$this->getShipment())
            throw new \Exception('No shipment available, unable to add tracking number');

        //try to update existing tracking number
        if ($trackingNumber)
        {
            foreach($this->getShipment()->getTracksCollection() as $tracking)
            {
                $tracking->setNumber($trackingNumber)->save();
                $this->_logger->log('Tracking # edited in shipment #'.$this->getShipment()->getId());
                return;
            }

            //no tracking to update, add it
            $this->_logger->log('Tracking # added to shipment #'.$this->getShipment()->getId());
            $this->_shipmentHelperFactory->create()->addTracking($this->getShipment(), $trackingNumber, '', '');
        }

        $this->notifyCustomer();

        $this->_logger->log('Change status to shipped for shipment #'.$this->getShipment()->getId());
        $this->setip_status(self::STATUS_SHIPPED)->save();
        return $this;
    }

    public function changeStatus($status)
    {
        $this->setip_status($status)->save();
    }

    public function getTrackingNumber()
    {
        if ($this->getShipment())
        {
            foreach($this->getShipment()->getTracksCollection() as $tracking)
                return $tracking->getNumber();
        }
    }

    public function notifyCustomer()
    {
        $this->_logger->log('Notify customer for shipment #'.$this->getShipment()->getId());
        $this->_shipmentHelperFactory->create()->notifyCustomer($this->getShipment());
    }


    /**
     * @param $orderInProgress
     */
    public function getDatasForExport()
    {
        $datas = [];

        foreach($this->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['preparation.'.$k] = $v;
        }

        foreach($this->getOrder()->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['order.'.$k] = $v;
        }

        foreach($this->getOrder()->getShippingAddress()->getData() as $k => $v)
        {
            if ((!is_array($v)) && (!is_object($v)))
                $datas['shippingaddress.'.$k] = $v;
        }

        $streetLines = $this->getOrder()->getShippingAddress()->getStreet();
        foreach($streetLines as $id => $line){
            $datas['shippingaddress.street_'.($id+1)] = $line;
        }

        if($datas['shippingaddress.region_id']){
            $region = $this->_regionFactory->create()->load($datas['shippingaddress.region_id']);
            if($region)
                $datas['shippingaddress.region_name'] = $region->getName();
        }

        if ($this->getShipment()) {
            foreach ($this->getShipment()->getData() as $k => $v) {
                if ((!is_array($v)) && (!is_object($v)))
                    $datas['shipment.' . $k] = $v;
            }
        }

        if ($this->getInvoice()) {
            foreach ($this->getInvoice()->getData() as $k => $v) {
                if ((!is_array($v)) && (!is_object($v)))
                    $datas['invoice.' . $k] = $v;
            }
        }

        return $datas;
    }

    /**
     *
     */
    public function getEstimatedWeight()
    {
        $weight = 0;
        foreach($this->getAllItems() as $item)
        {
            $weight += $item->getWeight() * $item->getipi_qty();
        }
        return $weight;
    }

    public function getProductsCount()
    {
        $count = 0;
        foreach($this->getAllItems() as $item)
        {
            $count += $item->getipi_qty();
        }
        return $count;
    }


    public function hydrateWithOrderInformation()
    {
        foreach($this->getOrder()->getData() as $k => $v)
        {
            if (!$this->getData($k))
                $this->setData($k, $v);
        }
    }

    public function getCarrierTemplate()
    {
        if (!$this->_carrierTemplate)
        {
            $this->_carrierTemplate = $this->_carrierTemplateHelper->getCarrierTemplateForOrder($this);
        }

        return $this->_carrierTemplate;
    }


}
