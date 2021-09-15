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

class StockRegistryProvider extends \Magento\CatalogInventory\Model\StockRegistryProvider
{

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId)
    {
        $this->log('getStockItem for product = '.$productId.' scopeId = '.$scopeId);

        $stockItem = $this->getStockRegistryStorage()->getStockItem($productId, $scopeId);
        if (null === $stockItem) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);

            //#############################################################
            $criteria->setScopeFilter($scopeId);    //BMS added line
            //#############################################################

            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                $this->getStockRegistryStorage()->setStockItem($productId, $scopeId, $stockItem);
            } else {
                $stockItem = $this->stockItemFactory->create();
            }
        }
        return $stockItem;
    }

    private function getStockRegistryStorage()
    {
        if (null === $this->stockRegistryStorage) {
            $this->stockRegistryStorage = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\StockRegistryStorage');
        }
        return $this->stockRegistryStorage;
    }

    public function getStockStatus($productId, $scopeId)
    {
        $this->log('getStockStatus for product = '.$productId.' scopeId = '.$scopeId);

        return parent::getStockStatus($productId, $scopeId);
    }

    protected function log($msg)
    {

    }

}