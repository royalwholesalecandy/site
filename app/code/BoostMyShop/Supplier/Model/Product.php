<?php

namespace BoostMyShop\Supplier\Model;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class Product
{
    protected $_orderItem;
    protected $_productAction;
    protected $_productResourceModel;
    protected $_productFactory;
    protected $_stockRegistryProvider;
    protected $_stockConfiguration;
    protected $_supplierProductFactory;
    protected $_currencyFactory;
    protected $_averageBuyingPrice;
    protected $_logger;
    protected $_config;


    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product $orderItem,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \BoostMyShop\Supplier\Model\Product\AverageBuyingPrice $averageBuyingPrice,
        \BoostMyShop\Supplier\Helper\Logger $logger,
        \BoostMyShop\Supplier\Model\Config $config,
        \BoostMyShop\Supplier\Model\ResourceModel\Product $productResourceModel
    ){
        $this->_orderItem = $orderItem;
        $this->_productAction = $productAction;
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_stockConfiguration = $stockConfiguration;
        $this->_productResourceModel = $productResourceModel;
        $this->_productFactory = $productFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_currencyFactory = $currencyFactory;
        $this->_averageBuyingPrice = $averageBuyingPrice;
        $this->_logger = $logger;
        $this->_config = $config;
    }

    public function updateQuantityToReceive($productId, $storeId = 0)
    {
        if ($this->productIsDeleted($productId))
            return;

        $qtyToReceive = $this->_orderItem->getQuantityToReceive($productId, $storeId);

        $this->_logger->log('Update qty to receive to '.$qtyToReceive.' for product #'.$productId.' and store #'.$storeId, 'product');

        $this->_productAction->updateAttributes([$productId], ['qty_to_receive' => $qtyToReceive], $storeId);
    }

    public function getStockDetails($productId)
    {
        if ($this->productIsDeleted($productId))
            return 'Product deleted';

        $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $this->_stockConfiguration->getDefaultScopeId());;

        $details = [];
        $details[] = __('Stock level :%1', $stockItem->getQty());
        $details[] = __('Low stock level :%1', $stockItem->getNotifyStockQty());

        return implode('<br>', $details);
    }

    public function getStockQuantity($productId)
    {
        $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $this->_stockConfiguration->getDefaultScopeId());;
        return $stockItem->getQty();
    }

    public function getLocation($productId, $warehouseId = 0)
    {
        $locationAttribute = $this->_config->getLocationAttribute();
        if ($locationAttribute)
        {
            $product = $this->_productFactory->create()->load($productId);
            return $product->getData($locationAttribute);
        }
    }

    public function getBarcode($productId)
    {
        $barcodeAttribute = $this->_config->getBarcodeAttribute();
        if ($barcodeAttribute)
        {
            $product = $this->_productFactory->create()->load($productId);
            return $product->getData($barcodeAttribute);
        }
    }

    public function assignBarcode($productId, $barcode)
    {
        $barcodeAttribute = $this->_config->getBarcodeAttribute();
        if ($barcodeAttribute)
        {
            $product = $this->_productFactory->create()->load($productId);
            $product->setData($barcodeAttribute, $barcode)->save();

            $this->_logger->log('Assign barcode '.$barcode.' to product #'.$productId, 'product');
        }
    }

    public function getSupplierDetails($productId)
    {
        $html = [];
        foreach($this->_supplierProductFactory->create()->getSuppliers($productId) as $item)
        {
            $label = $item->getsup_name();
            if ($item->getsp_primary())
                $label = '<b><u>'.$label.'</u></b>';
            if ($item->getsp_price() > 0)
                $label .= ' : '.$this->formatPrice($item->getsp_price(), $item->getsup_currency());

            $html[] = $label;
        }
        return implode('<br>', $html);
    }

    public function getCost($productId)
    {
        $product = $this->_productFactory->create()->load($productId);
        return $product->getCost();
    }

    public function updateCost($productId)
    {
        $quantity = $this->getStockQuantity($productId);
        $this->_logger->log('Calculate cost for product #'.$productId.', quantity considered = '.$quantity);
        if ($quantity <= 0)
            return;

        $value = $this->_averageBuyingPrice->calculateValue($productId, $quantity);
        if ($value > 0)
            $this->_productAction->updateAttributes([$productId], ['cost' => $value], 0);
        $this->_logger->log('Calculated cost for product #'.$productId.' is '.$value);

        return $value;
    }

    public function productIsDeleted($productId)
    {
        $result = $this->_productResourceModel->productIsDeleted($productId);
        return $result;
    }

    protected function formatPrice($price, $currencyCode)
    {
        $currency = $this->_currencyFactory->create()->load($currencyCode);
        return $currency->format($price, [], false);
    }



}