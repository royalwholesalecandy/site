<?php

namespace BoostMyShop\AdvancedStock\Model;


class ExtendedSalesFlatOrderItem extends \Magento\Framework\Model\AbstractModel
{
    protected $_orderItemFactory;
    protected $_orderItem;
    protected $_logger;
    protected $_router;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \BoostMyShop\AdvancedStock\Model\Router $router,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_orderItemFactory = $orderItemFactory;
        $this->_logger = $logger;
        $this->_router = $router;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\ExtendedSalesFlatOrderItem');
    }

    public function loadByItemId($itemId)
    {
        return $this->load($itemId, 'esfoi_order_item_id');
    }

    public function createFromOrderItem($order, $orderItem)
    {
        $warehouseId = $this->_router->getWarehouseIdForOrderItem($order, $orderItem);

        $this->setesfoi_order_item_id($orderItem->getId());
        $this->setesfoi_warehouse_id($warehouseId);
        $this->setesfoi_qty_reserved(0);
        $this->setesfoi_qty_to_ship($this->getQuantityToShip());
        $this->save();

        return $this;
    }

    public function getOrderItem()
    {
        if (!$this->_orderItem)
        {
            $this->_orderItem = $this->_orderItemFactory->create()->load($this->getesfoi_order_item_id());
        }
        return $this->_orderItem;
    }

    public function getQuantityToShip()
    {
        $qty = $this->getOrderItem()->getSimpleQtyToShip();

        if ($this->getOrderItem()->getparent_item_id())
        {
            $parent = $this->_orderItemFactory->create()->load($this->getOrderItem()->getparent_item_id());
            switch($parent->getProductType())
            {
                case 'configurable':
                    $qty = $parent->getSimpleQtyToShip();
                    break;
            }

        }
        return $qty;
    }

    public function beforeSave()
    {
        parent::beforeSave();

        if ($this->getesfoi_qty_reserved() > $this->getesfoi_qty_to_ship()) {
            $this->setesfoi_qty_reserved($this->getesfoi_qty_to_ship());
            $this->_logger->log('Force reserved quantity to '.$this->getesfoi_qty_to_ship().' for order item #'.$this->getesfoi_order_item_id(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogReservation);
        }

    }

    public function afterSave()
    {
        parent::afterSave();

        //dispatch event when order item warehouse changes
        if ($this->getData('esfoi_warehouse_id') != $this->getOrigData('esfoi_warehouse_id')) {
            $oldWarehouseId = $this->getOrigData('esfoi_warehouse_id');
            $newWarehouseId = $this->getData('esfoi_warehouse_id');

            $this->setOrigData('esfoi_warehouse_id', $newWarehouseId);  //prevent recursive call

            $this->_eventManager->dispatch('advancedstock_order_item_warehouse_change', ['extended_item' => $this, 'old_warehouse_id' => $oldWarehouseId, 'new_warehouse_id' => $newWarehouseId]);
        }
    }

    /**
     * Update quantity to ship, depending of parent (if exists)
     *
     * @param $orderItem
     * @return $this
     */
    public function updateQtyToShip()
    {
        $qty = $this->getQuantityToShip();
        $this->setesfoi_qty_to_ship($qty);
        return $this;
    }

}
