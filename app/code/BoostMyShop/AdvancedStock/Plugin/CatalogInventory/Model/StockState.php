<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;

class StockState extends \Magento\CatalogInventory\Model\StockState
{
    protected $_logger;
    protected $_storeManager;

    public function __construct(
        StockStateProviderInterface $stockStateProvider,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \BoostMyShop\AdvancedStock\Helper\Logger $logger
    ) {
        parent::__construct($stockStateProvider, $stockRegistryProvider, $stockConfiguration);

        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return bool
     */
    public function verifyStock($productId, $scopeId = null)
    {
        $scopeId = $this->_storeManager->getStore()->getWebsiteId();

        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $result = $this->stockStateProvider->verifyStock($stockItem);
        $this->_logger->log('StockState::verifyStock for product #'.$productId.' and scopeId = '.$scopeId.' : '.($result ? 'true' : 'false'), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
        return $result;
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return bool
     */
    public function verifyNotification($productId, $scopeId = null)
    {
        $scopeId = $this->_storeManager->getStore()->getWebsiteId();

        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        return $this->stockStateProvider->verifyNotification($stockItem);
    }

    /**
     * Check quantity
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @exception \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function checkQty($productId, $qty, $scopeId = null)
    {
        $scopeId = $this->_storeManager->getStore()->getWebsiteId();

        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $result = $this->stockStateProvider->checkQty($stockItem, $qty);

        $this->_logger->log('StockState::checkQty for '.$qty.'x product #'.$productId.' and scopeId = '.$scopeId.' : '.($result ? 'true' : 'false'), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);

        return $result;
    }

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @return float
     */
    public function suggestQty($productId, $qty, $scopeId = null)
    {
        $scopeId = $this->_storeManager->getStore()->getWebsiteId();

        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $result = $this->stockStateProvider->suggestQty($stockItem, $qty);
        $this->_logger->log('StockState::suggestQty for '.$qty.'x product #'.$productId.' and scopeId = '.$scopeId.' : '.($result ? 'true' : 'false'), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
        return $result;
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param int $productId
     * @param int $scopeId
     * @return float
     */
    public function getStockQty($productId, $scopeId = null)
    {
        $scopeId = $this->_storeManager->getStore()->getWebsiteId();

        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $value = $this->stockStateProvider->getStockQty($stockItem);
        $this->_logger->log('StockState::getStockQty product #'.$productId.' and scopeId = '.$scopeId.' : stockitemid='.$stockItem->getId().' and qty = '.$value, \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
        return $value;
    }

    /**
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @return \Magento\Framework\DataObject
     */
    public function checkQtyIncrements($productId, $qty, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        }
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
        return $this->stockStateProvider->checkQtyIncrements($stockItem, $qty);
    }

    /**
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int $scopeId
     * @return int
     */
    public function checkQuoteItemQty($productId, $itemQty, $qtyToCheck, $origQty, $scopeId = null)
    {
        $scopeId = $this->_storeManager->getStore()->getWebsiteId();

        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $result = $this->stockStateProvider->checkQuoteItemQty($stockItem, $itemQty, $qtyToCheck, $origQty);
        $this->_logger->log('StockState::checkQuoteItemQty for '.$qtyToCheck.'x product #'.$productId.' and scopeId = '.$scopeId.' : '.($result ? 'true' : 'false'), \BoostMyShop\AdvancedStock\Helper\Logger::kLogInventoryCore);
        return $result;
    }
}
