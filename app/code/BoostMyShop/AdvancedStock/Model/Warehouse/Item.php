<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


class Item extends \Magento\Framework\Model\AbstractModel
{
    protected $_logger;
    protected $_config;
    protected $_stockRegistry;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_logger = $logger;
        $this->_config = $config;
        $this->_stockRegistry = $stockRegistry;
    }

    public function beforeSave()
    {
        parent::beforeSave();

        $availableQuantity = $this->getwi_physical_quantity() - $this->getwi_quantity_to_ship();
        if ($availableQuantity < 0)
            $availableQuantity = 0;
        $this->setwi_available_quantity($availableQuantity);

        //prevent NULL fields in DB
        $fields = ['wi_physical_quantity', 'wi_available_quantity', 'wi_reserved_quantity'];
        foreach($fields as $field)
        {
            if ($this->getData($field) == null)
                $this->setData($field, 0);
        }

        return $this;
    }

    public function afterSave()
    {
        parent::afterSave();

        if  (
            $this->getData('wi_available_quantity') != $this->getOrigData('wi_available_quantity')
            ||
            $this->getData('wi_quantity_to_ship') != $this->getOrigData('wi_quantity_to_ship')  //required using multiple warehouses
            )
            $this->_eventManager->dispatch('advancedstock_warehouse_item_available_quantity_after_change', ['warehouse_item' => $this]);

        if (
            (($this->getReservableQuantity() > 0) && ($this->getwi_reserved_quantity() < $this->getwi_quantity_to_ship()))
            || ($this->getwi_reserved_quantity() > $this->getwi_quantity_to_ship())
            || ($this->getwi_reserved_quantity() > $this->getwi_physical_quantity())
        )
            $this->_eventManager->dispatch('advancedstock_warehouse_item_reservation_to_process', ['warehouse_item' => $this]);
    }

    public function loadByProductWarehouse($productId, $warehouseId)
    {
        $this->_getResource()->loadByProductWarehouse($this, $productId, $warehouseId);
        $this->setOrigData();
        if (!$this->getId())
        {
            $this->setwi_product_id($productId);
            $this->setwi_warehouse_id($warehouseId);
        }
        else
            $this->_hasDataChanges = false;
        return $this;
    }

    /**
     * Update physical quantity based on stock movements
     *
     * @param bool $save
     * @return $this
     */
    public function updatePhysicalQuantity($save = false)
    {
        $qty = $this->calculatePhysicalQuantityFromStockMovements();

        $this->_logger->log('Calculate quantity for #'.$this->getwi_product_id().' in warehouse #'.$this->getwi_warehouse_id().' : '.$qty, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);

        $this->setwi_physical_quantity($qty);
        if ($save)
            $this->save();
        return $this;
    }

    public function calculatePhysicalQuantityFromStockMovements()
    {
        return $this->_getResource()->calculatePhysicalQuantityFromStockMovements($this->getwi_warehouse_id(), $this->getwi_product_id());
    }

    public function getReservableQuantity()
    {
        if ($this->productManagesStock())
            $value = ($this->getwi_physical_quantity() - $this->getwi_reserved_quantity());
        else
            $value = 9999;
        return max($value, 0);
    }

    public function createRecord($productId, $warehouseId = 1)
    {
        $this->setwi_product_id($productId);
        $this->setwi_warehouse_id($warehouseId);
        $this->save();

        return $this;
    }

    public function getWarningStockLevel()
    {
        if ($this->getwi_use_config_warning_stock_level())
            return $this->_config->getDefaultWarningStockLevel();
        else
            return $this->getwi_warning_stock_level();
    }

    public function getIdealStockLevel()
    {
        if ($this->getwi_use_config_ideal_stock_level())
            return $this->_config->getDefaultIdealStockLevel();
        else
            return $this->getwi_ideal_stock_level();
    }

    public function getQtyNeededForIdealStockLevel()
    {
        if (!$this->productManagesStock())
            return 0;

        if ($this->getwi_available_quantity() < $this->getWarningStockLevel())
            return max($this->getIdealStockLevel() - $this->getwi_available_quantity(), 0);
        else
            return 0;
    }

    public function getQtyNeededForOrders()
    {
        if (!$this->productManagesStock())
            return 0;

        if ($this->getwi_physical_quantity() < $this->getwi_quantity_to_ship())
            return max($this->getwi_quantity_to_ship() - $this->getwi_physical_quantity(), 0);
        else
            return 0;
    }

    public function productManagesStock()
    {
        $stockitem = $this->_stockRegistry->getStockItem($this->getwi_product_id());
        return $stockitem->getManageStock();
    }

}