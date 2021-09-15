<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Helper;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\DailyDeal\Model\DealFactory;

/**
 * Class Data
 * @package Mageplaza\DailyDeal\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'dailydeal';

    /**
     * @var ProductFactory
     */
    public $_productFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Mageplaza\DailyDeal\Model\DealFactory
     */
    protected $_dealFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Data constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param StockRegistryInterface $stockRegistry
     * @param DealFactory $dealFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
        DealFactory $dealFactory,
        Registry $registry
    )
    {
        $this->_productFactory = $productFactory;
        $this->_stockRegistry  = $stockRegistry;
        $this->_dealFactory    = $dealFactory;
        $this->_registry       = $registry;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Get Price of product by id
     * @param $productId
     * @return float|int
     */
    public function getProductPrice($productId)
    {
        if ($productId) {
            $product          = $this->_productFactory->create();
            $productPriceById = $product->load($productId)->getPrice();

            return $productPriceById;
        }

        return 0;
    }

    /**
     * Get Qty of Product by sku
     * @param $sku
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductQty($sku)
    {
        if ($sku) {
            return $this->_stockRegistry->getStockItemBySku($sku)->getQty();
        }

        return 0;
    }

    /**
     * Get Qty of Product by Id
     *
     * @param $productId
     * @return float
     */
    public function getStockItemById($productId)
    {
        return $this->_stockRegistry->getStockItem($productId)->getQty();
    }

    /**
     * @param null $productId
     * @return \Magento\Framework\DataObject
     */
    public function getProductDeal($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();

        $dealCollection = $this->_dealFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('product_id', ['eq' => $productId]);

        return $dealCollection->getSize() ? $dealCollection->getFirstItem() : new DataObject();
    }

    /**
     * Check product deal by product id
     *
     * @param null $productId
     * @return bool
     */
    public function checkDealProduct($productId = null)
    {
        $productId = $productId ?$productId: $this->getCurrentProduct()->getId();
            
        return $this->checkStatusDeal($productId);
    }

    /**
     * Get Product Id by Sku
     *
     * @param $sku
     * @return mixed
     */
    public function getProductIdBySku($sku)
    {
        $dealCollection = $this->_dealFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('product_sku', ['eq' => $sku]);

        return $dealCollection->getFirstItem()->getProductId();
    }

    /**
     * Check running deal
     *
     * @param $productId
     * @return bool
     */
    public function checkStatusDeal($productId)
    {
        $dealCollection = $this->getProductDeal($productId);
        $currentDate    = date('d-m-Y H:i:s');
        $status         = $dealCollection->getStatus();
        $dateFrom       = $dealCollection->getDateFrom();
        $dateTo         = $dealCollection->getDateTo();
        $dealQty        = $dealCollection->getDealQty();
        $saleQty        = $dealCollection->getSaleQty();

        return $status == 1 &&
            $dealQty > $saleQty &&
            strtotime($dateTo) >= strtotime($currentDate) &&
            strtotime($dateFrom) <= strtotime($currentDate);
    }

    /**
     * Check Ended Deal
     *
     * @param $productId
     * @return bool
     */
    public function checkEndedDeal($productId)
    {
        $dealCollection = $this->getProductDeal($productId);
        $currentDate    = date('d-m-Y H:i:s');
        $status         = $dealCollection->getStatus();
        $dateTo         = $dealCollection->getDateTo();
        $dealQty        = $dealCollection->getDealQty();
        $saleQty        = $dealCollection->getSaleQty();

        return $status == 1 && ($dealQty <= $saleQty || strtotime($dateTo) < strtotime($currentDate));
    }

    /**
     * Check deal disable
     *
     * @param $productId
     * @return bool
     */
    public function checkDisableDeal($productId)
    {
        $dealCollection = $this->getProductDeal($productId);

        return $dealCollection->getStatus() != null && $dealCollection->getStatus() == 0;
    }

    /**
     * Get child product Ids of configuration product by parent id
     *
     * @param null $productId
     * @return array
     */
    public function getChildConfigurableProductIds($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();

        $childIds = [];
        $product  = $this->_productFactory->create()->load($productId);
        if ($product->getTypeId() === 'configurable') {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                $childId = $child->getID();
                if ($this->checkStatusDeal($childId)) {
                    array_push($childIds, $childId);
                }
            }
        }

        return $childIds;
    }

    /**
     * Check configuration product
     *
     * @param $productId
     * @return bool
     */
    public function checkDealConfigurableProduct($productId)
    {
        if ($this->getStockItemById($productId) > 0) {
            return (bool)$this->getChildConfigurableProductIds($productId);
        }

        return false;
    }

    /**
     * Get parent product id by child product id
     *
     * @param $productId
     * @return mixed
     */
    public function getParentIdByChildId($productId)
    {
        if ($this->getStockItemById($productId) > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productConfig = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($productId);
            if (isset($productConfig[0])) {
                return $productConfig[0];
            }

            $productGrouped = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')->getParentIdsByChild($productId);
            if (isset($productGrouped[0])) {
                return $productGrouped[0];
            }

            return $productId;
        }

        return 0;
    }

    /**
     * Get parent product ids by child product ids
     *
     * @param $productIds
     * @return array
     */
    public function getProductIdsParent($productIds)
    {
        $ids = [];

        foreach ($productIds as $productId) {
            $ids[] = $this->getParentIdByChildId($productId);
        }

        return $ids;
    }

    /**
     * get Deal Price
     *
     * @param $id
     * @return mixed
     */
    public function getDealPrice($id)
    {
        $deal            = $this->getProductDeal($id);
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
        $price           = $this->storeManager->getStore()->getBaseCurrency()->convert($deal->getDealPrice(), $currentCurrency);

        return $price;
    }

    /**
     * get Default Deal Price
     *
     * @param $id
     * @return mixed
     */
    public function getDefaultDealPrice($id)
    {
        $deal = $this->getProductDeal($id);

        return $deal->getDealPrice();
    }

    /**
     * Get format price
     *
     * @param $price
     * @return mixed
     */
    public function formatPrice($price)
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->format($price);
    }

    /**
     * get Current Product
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * get current category
     *
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}