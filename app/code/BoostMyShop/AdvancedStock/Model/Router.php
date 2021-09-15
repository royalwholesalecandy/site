<?php

    namespace BoostMyShop\AdvancedStock\Model;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Router
{
    protected $_storeCollectionFactory;
    protected $_storeWarehouseCollectionFactory;
    protected $_routingStore;
    protected $_routingWarehouse;
    protected $_routingProduct;
    protected $_cache = null;
    protected $_config = null;
    protected $_warehouseCollectionFactory = null;
    protected $_websiteCollectionFactory = null;
    protected $_stockRegistryProvider;
    protected $_stockConfiguration;
    protected $_websiteIds;
    protected $_warehouses;
    protected $_pendingOrdersCollectionFactory;
    protected $_warehouseItemFactory;
    protected $_storeFactory;
    protected $_logger;

    protected static $_warehousesForSalesCache = array();

    protected static $_stores = [];

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store\CollectionFactory $storeCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Routing\StoreFactory $routingStore,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \BoostMyShop\AdvancedStock\Model\Routing\Store\WarehouseFactory $routingWarehouse,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Store\Warehouse\CollectionFactory $storeWarehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Routing\Product $routingProduct,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Product\PendingOrders\CollectionFactory $pendingOrdersCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    )
    {
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_storeWarehouseCollectionFactory = $storeWarehouseCollectionFactory;
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_routingStore = $routingStore;
        $this->_routingProduct = $routingProduct;
        $this->_routingWarehouse = $routingWarehouse;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_stockConfiguration = $stockConfiguration;
        $this->_pendingOrdersCollectionFactory = $pendingOrdersCollectionFactory;
        $this->_config = $config;
        $this->_warehouseItemFactory = $warehouseItemFactory;
        $this->_storeFactory = $storeFactory;
        $this->_logger = $logger;
    }


    public function getWarehouseIdForOrderItem($order, $orderItem)
    {
        $routingMode = $this->getRoutingMode($order->getStoreId());
        $this->_logger->log('Routing mode for '.$order->getId().'/'.$orderItem->getId().' is '.$routingMode, \BoostMyShop\AdvancedStock\Helper\Logger::kLogRouting);
        $productId = $orderItem->getproduct_id();

        $warehouseId = null;
        switch($routingMode)
        {
            case \BoostMyShop\AdvancedStock\Model\Routing\Store\Mode::alwaysBestPriority:
                $store = $this->getStore($order->getStoreId());
                foreach($this->getWarehousesForShipment($store->getWebsiteId()) as $id => $data)
                {
                    if ($data['rsw_priority'] == 1) {
                        $this->_logger->log('Warehouse with priority 1 for  '.$order->getId().'/'.$orderItem->getId().' is '.$id, \BoostMyShop\AdvancedStock\Helper\Logger::kLogRouting);
                        $warehouseId = $id;
                    }
                }
                break;
            case \BoostMyShop\AdvancedStock\Model\Routing\Store\Mode::withStockOrderByPriority:
                $store = $this->getStore($order->getStoreId());
                foreach($this->getWarehousesForShipment($store->getWebsiteId()) as $id => $data)
                {
                    if (!$warehouseId)
                    {
                        $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $id);
                        if  ($warehouseItem->getwi_available_quantity() >= $orderItem->getqty_ordered()) {
                            $this->_logger->log('Warehouse #'.$id.' has stock for '.$order->getId().'/'.$orderItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogRouting);
                            $warehouseId = $id;
                        }
                        else
                            $this->_logger->log('Warehouse #'.$id.' has not enough stock ('.$warehouseItem->getwi_available_quantity().') for '.$order->getId().'/'.$orderItem->getId(), \BoostMyShop\AdvancedStock\Helper\Logger::kLogRouting);
                    }
                }
                break;
        }

        if (!$warehouseId)
        {
            if ($this->getPrimaryWarehouse())
                $warehouseId = $this->getPrimaryWarehouse()->getId();
            if (!$warehouseId)
                $warehouseId = $this->getWarehouses()->getFirstItem()->getId();
            $this->_logger->log('Can not determinate warehouse for '.$order->getId().'/'.$orderItem->getId().', use warehouse '.$warehouseId, \BoostMyShop\AdvancedStock\Helper\Logger::kLogRouting);
        }

        return $warehouseId;
    }

    public function getRoutingMode($storeId)
    {
        $store = $this->getStore($storeId);
        $conf = $this->getStoreConfiguration($store->getWebsiteId(), $store->getGroupId(), $storeId);
        return $conf->getrs_routing_mode();
    }

    public function getStoreConfiguration($websiteId, $groupId, $storeId)
    {
        $cache = $this->getCache();

        $requestedKey = $websiteId.'_'.$groupId.'_'.$storeId;

        $keys = [];
        $keys[] = $requestedKey;
        $keys[] = $websiteId.'_'.$groupId.'_0';
        $keys[] = $websiteId.'_0_0';
        $keys[] = '0_0_0';

        foreach($keys as $key)
        {
            if (isset($cache['store'][$key])) {
                $result = $cache['store'][$key];
                if ($key != $requestedKey)
                    $result->setrs_use_default(1);
                return $result;
            }
        }

        throw new \Exception('Unable to load default routing configuration for store');
    }

    public function getStoreWarehouseConfiguration($websiteId, $groupId, $storeId, $warehouseId)
    {
        $cache = $this->getCache();

        $requestedKey = $websiteId.'_'.$groupId.'_'.$storeId.'_'.$warehouseId;

        $keys = [];
        $keys[] = $requestedKey;
        $keys[] = $websiteId.'_'.$groupId.'_0_'.$warehouseId;
        $keys[] = $websiteId.'_0_0_'.$warehouseId;
        $keys[] = '0_0_0_'.$warehouseId;

        foreach($keys as $key)
        {
            if (isset($cache['warehouse'][$key])) {
                $result = $cache['warehouse'][$key];
                if ($key != $requestedKey)
                    $result->setrsw_use_default(1);
                return $result;
            }
        }

        throw new \Exception('Unable to load default routing configuration for warehouse');
    }

    public function getCache()
    {
        if (!$this->_cache)
        {
            $this->_cache = ['store' => [], 'warehouse' => []];

            $collection = $this->_storeCollectionFactory->create()->addFieldToFilter('rs_use_default', array('neq' => 1));
            foreach($collection as $item)
            {
                $key = $item->getrs_website_id().'_'.$item->getrs_group_id().'_'.$item->getrs_store_id();
                $this->_cache['store'][$key] = $item;
            }
            if (!isset($this->_cache['store']['0_0_0']))
                $this->_cache['store']['0_0_0'] = $this->_routingStore->create()->getDefaultItem();

            $collection = $this->_storeWarehouseCollectionFactory->create()->addFieldToFilter('rsw_use_default', array('neq' => 1));
            foreach($collection as $item)
            {
                $key = $item->getrsw_website_id().'_'.$item->getrsw_group_id().'_'.$item->getrsw_store_id().'_'.$item->getrsw_warehouse_id();
                $this->_cache['warehouse'][$key] = $item;
            }
            foreach($this->getWarehouses() as $item)
            {
                $key = '0_0_0_'.$item->getId();
                if (!isset($this->_cache['warehouse'][$key]))
                    $this->_cache['warehouse'][$key] = $this->_routingWarehouse->create()->getDefaultItem($item->getId());
            }

        }
        return $this->_cache;
    }

    /**
     * Return websites ids for which this warehouse acts for sellable quantity
     *
     * @param $warehouseId
     */
    public function getWebsitesForSales($warehouseId)
    {
        $websites = [];

        foreach($this->getWebsiteIds() as $websiteId)
        {
            $config = $this->getStoreWarehouseConfiguration($websiteId, 0, 0, $warehouseId);
            if ($config)
            {
                if (isset($config['rsw_use_for_sales']) && $config['rsw_use_for_sales'])
                    $websites[] = $websiteId;
            }
        }

        return $websites;
    }

    public function getWarehousesForSales($websiteId)
    {
        if (isset(self::$_warehousesForSalesCache[$websiteId]))
            return self::$_warehousesForSalesCache[$websiteId];

        $warehouses = [];

        foreach($this->getWarehouses() as $warehouse)
        {
            $config = $this->getStoreWarehouseConfiguration($websiteId, 0, 0, $warehouse->getId());
            if ($config)
            {
                if (isset($config['rsw_use_for_sales']) && $config['rsw_use_for_sales'])
                    $warehouses[] = $warehouse->getId();
            }
        }

        self::$_warehousesForSalesCache[$websiteId] = $warehouses;
        return $warehouses;
    }

    public function getWarehousesForShipment($websiteId)
    {

        $warehouses = [];

        foreach($this->getWarehouses() as $warehouse)
        {
            $config = $this->getStoreWarehouseConfiguration($websiteId, 0, 0, $warehouse->getId());
            if ($config)
            {
                if (isset($config['rsw_use_for_shipments']) && $config['rsw_use_for_shipments'])
                    $warehouses[$warehouse->getId()] = $config;
            }
        }

        uasort($warehouses, array('\BoostMyShop\AdvancedStock\Model\Router', 'sortWarehousesPerPriority'));

        return $warehouses;
    }

    public static function sortWarehousesPerPriority($a, $b)
    {
        return ($a['rsw_priority'] > $b['rsw_priority']);
    }

    public function updateSalableQuantity($websiteId, $productId)
    {
        $hasChanged = false;

        $qty = (int)$this->getSellableQuantity($websiteId, $productId);
        $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $websiteId);
        if ((int)$stockItem->getQty() != (int)$qty)
            $hasChanged = true;
        if ($stockItem->getQty() == null)
            $hasChanged = true;
        $stockItem->setQty($qty);
        $this->_logger->log('Load stock item for updateSalableQuantity for website #'.$websiteId.' and product #'.$productId.' to store qty '.$qty.' (id='.$stockItem->getId().')', \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
        if ($stockItem->getQty() > $stockItem->getMinQty())
        {
            if (!$stockItem->getIsInStock())
                $hasChanged = true;
            $stockItem->setIsInStock(true);
            $stockItem->setStockStatusChangedAutomaticallyFlag(true);
        }

        if ($hasChanged)
            $stockItem->save();

        $stockItem->clearInstance();
    }

    public function getSellableQuantity($websiteId, $productId)
    {
        $warehouses = $this->getWarehousesForSales($websiteId);
        $qty = $this->_routingProduct->calculateSellableQty($warehouses, $productId);
        $this->_logger->log('Calculate sellableQuantity for website #'.$websiteId.' and product #'.$productId.' : '.$qty, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventory);
        return $qty;
    }

    //todo : move elsewehere (dont know where...)
    public function updateQuantityToShip($productId, $warehouseId)
    {

        $qty = $this->getQuantityToShip($productId, $warehouseId);

        $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);

        $warehouseItem->setwi_quantity_to_ship($qty)->save();

        return $this;
    }

    public function getQuantityToShip($productId, $warehouseId)
    {
        $qty = $this->_pendingOrdersCollectionFactory
            ->create()
            ->addExtendedDetails()
            ->addOrderDetails()
            ->addProductFilter($productId)
            ->addWarehouseFilter($warehouseId)
            ->addStatusesFilter($this->_config->getPendingOrderStatuses())
            ->getTotalQuantityToShip();
        if (!$qty || $qty < 0)
            $qty = 0;
        $this->_logger->log("Calculate qty to ship for product #".$productId." and warehouse #".$warehouseId." :".$qty);
        return $qty;
    }

    //todo : move elsewehere (dont know where...)
    public function updateReservedQuantity($productId, $warehouseId)
    {
        $qty = $this->getReservedQuantity($productId, $warehouseId);
        $warehouseItem = $this->_warehouseItemFactory->create()->loadByProductWarehouse($productId, $warehouseId);
        $warehouseItem->setwi_reserved_quantity($qty)->save();

        $this->_logger->log("Save reserved qty for product #".$productId." and warehouse #".$warehouseId." :".$qty);

        return $this;
    }

    public function getReservedQuantity($productId, $warehouseId)
    {
        $qty = $this->_pendingOrdersCollectionFactory
            ->create()
            ->addExtendedDetails()
            ->addOrderDetails()
            ->addProductFilter($productId)
            ->addWarehouseFilter($warehouseId)
            ->addStatusesFilter($this->_config->getPendingOrderStatuses())
            ->getTotalQuantityReserved();

        $this->_logger->log("Calculate reserved qty for product #".$productId." and warehouse #".$warehouseId." : ".$qty);

        if (!$qty || $qty < 0)
            $qty = 0;

        return $qty;
    }

    public function getPrimaryWarehouse($websiteId = null)
    {
        foreach($this->getWarehouses() as $w)
        {
            if ($w->getw_is_primary())
                return $w;
        }
    }

    protected function getWebsiteIds()
    {
        if (!$this->_websiteIds) {
            $this->_websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();
            $this->_websiteIds[] = 0;
        }
        return $this->_websiteIds;
    }

    protected function getWarehouses()
    {
        if (!$this->_warehouses)
            $this->_warehouses = $this->_warehouseCollectionFactory->create();
        return $this->_warehouses;
    }

    public function getStore($storeId)
    {
        if (!isset(self::$_stores[$storeId]))
        {
            self::$_stores[$storeId] = $this->_storeFactory->create()->load($storeId);
        }
        return self::$_stores[$storeId];
    }

}
