<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model\Plugin;

class AfterProductLoad extends \Magento\CatalogInventory\Model\Plugin\AfterProductLoad
{
    protected $_storeFactory;
    protected $stockRegistry;
    protected $_productMetadataInterface;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
    ) {
        $this->_storeFactory = $storeFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_productMetadataInterface = $productMetadataInterface;

        //call parent constructor depending of magento version
        $constructor = '__construct';   //use to prevent magento constructor integrity to raise an error because he does not select the right constructor...
        if ($this->isM22())
            parent::$constructor($stockRegistry);
        else
            parent::$constructor($stockRegistry, $productExtensionFactory);
    }

    /**
     * Identify Magento version based on
     *
     * @return bool
     */
    public function isM22()
    {
        $version = $this->_productMetadataInterface->getVersion();
        $version = substr($version, 0, 3);

        return ($version == '2.2');
    }

    //Original magento class doesnt consider the product store to pass the website to the getStockItem method
    public function afterLoad(\Magento\Catalog\Model\Product $product)
    {

        $productExtension = $product->getExtensionAttributes();
        if (($productExtension === null) && (!$this->isM22())) {
            $productExtension = $this->productExtensionFactory->create();
        }

        $websiteId = null;
        if ($product->getStoreId())
        {
            $store = $this->_storeFactory->create()->load($product->getStoreId());
            $websiteId = $store->getwebsite_id();
        }

        // stockItem := \Magento\CatalogInventory\Api\Data\StockItemInterface
        $productExtension->setStockItem($this->stockRegistry->getStockItem($product->getId(), $websiteId));
        $product->setExtensionAttributes($productExtension);
        return $product;
    }

}
