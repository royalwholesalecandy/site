<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

class StockRegistry extends \Magento\CatalogInventory\Model\StockRegistry
{
    /**
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeId = null)
    {
        $this->log('getStock for scopeId = '.$scopeId);

        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }

        return $this->stockRegistryProvider->getStock($scopeId);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId = null)
    {

        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        $si = $this->stockRegistryProvider->getStockItem($productId, $scopeId);

        $this->log('getStockItem for product = '.$productId.' scopeId = '.$scopeId.' : qty = '.$si->getQty().', is_in_stock = '.$si->getIsInStock());

        return $si;
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $scopeId = null)
    {
        $this->log('getStockItemBySku for product = '.$productSku.' scopeId = '.$scopeId);

        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        $productId = $this->resolveProductId($productSku);
        return $this->stockRegistryProvider->getStockItem($productId, $scopeId);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId = null)
    {
        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        $value = $this->stockRegistryProvider->getStockStatus($productId, $scopeId);

        $this->log('getStockStatus for product = '.$productId.' scopeId = '.$scopeId.' : status = '.$value->getStockStatus().', qty = '.$value->getQty());

        return $value;
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $scopeId = null)
    {
        $this->log('getStockStatusBySku for product = '.$productSku.' scopeId = '.$scopeId);

        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        $productId = $this->resolveProductId($productSku);
        return $this->getStockStatus($productId, $scopeId);
    }

    /**
     * Retrieve Product stock status
     * @param int $productId
     * @param int $scopeId
     * @return int
     */
    public function getProductStockStatus($productId, $scopeId = null)
    {
        $this->log('getProductStockStatus for product = '.$productId.' scopeId = '.$scopeId);

        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        $stockStatus = $this->getStockStatus($productId, $scopeId);
        return $stockStatus->getStockStatus();
    }

    /**
     * @param string $productSku
     * @param null $scopeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $scopeId = null)
    {
        $this->log('getProductStockStatusBySku for product = '.$productSku.' scopeId = '.$scopeId);

        if (!$scopeId) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        $productId = $this->resolveProductId($productSku);
        return $this->getProductStockStatus($productId, $scopeId);
    }

    protected function log($msg)
    {

    }
}
