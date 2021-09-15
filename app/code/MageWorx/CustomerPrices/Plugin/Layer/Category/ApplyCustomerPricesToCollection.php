<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\Layer\Category;

use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Category\CollectionFilter;
use MageWorx\CustomerPrices\Model\ResourceModel\CustomerPrices as ResourceCustomerPrices;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\ObjectManagerInterface;

class ApplyCustomerPricesToCollection
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var ResourceCustomerPrices
     */
    protected $customerPricesResourceModel;

    /**
     * @var AppResource
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ApplyCustomerPricesToCollection constructor.
     *
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param HelperCalculate $helperCalculate
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ResourceCustomerPrices $customerPricesResourceModel
     * @param AppResource $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        HelperCalculate $helperCalculate,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        ResourceCustomerPrices $customerPricesResourceModel,
        AppResource $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->helperData                  = $helperData;
        $this->helperCustomer              = $helperCustomer;
        $this->helperCalculate             = $helperCalculate;
        $this->productVisibility           = $productVisibility;
        $this->catalogConfig               = $catalogConfig;
        $this->customerPricesResourceModel = $customerPricesResourceModel;
        $this->resource                    = $resource;
        $this->connection                  = $this->resource->getConnection();
        $this->moduleManager               = $moduleManager;
        $this->objectManager               = $objectManager;
    }

    /**
     * @param CollectionFilter $object
     * @param callable $proceed
     * @param Collection $collection
     * @param Category $category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundFilter(
        CollectionFilter $object,
        callable $proceed,
        Collection $collection,
        Category $category
    ) {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($category->getId())
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());

        $customerId = $this->helperCustomer->getCurrentCustomerId();
        if ($customerId) {
            $select    = $collection->getSelect();
            $tableName = $this->resource->getTableName('mageworx_catalog_product_index_price');

            $select->joinLeft(
                ['mageworx_price_index' => $tableName],
                'mageworx_price_index.entity_id = e.entity_id AND mageworx_price_index.customer_id = ' . $customerId
            );

            $newIsSalable = $this->getIsSalable();

            $newPrice = "IF(
                            price_index.tier_price IS NOT NULL, 
                            IF(
                               mageworx_price_index.min_price IS NOT NULL,
                               LEAST(mageworx_price_index.min_price, price_index.tier_price),
                               LEAST(price_index.min_price, price_index.tier_price)
                              ),
                            IF(
                               mageworx_price_index.min_price IS NOT NULL,
                               mageworx_price_index.min_price,
                               price_index.min_price
                              )
                           )";
			/**
			 * 2019-12-10 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
			 * «images are not loading on the when you log into your account online,
			 * it loads fine before you log in, it is when you log in after they disappear.»:
			 * https://github.com/royalwholesalecandy/core/issues/21
			 */
            $modifiedColumns = [
			   'entity_id'          => 'price_index.entity_id',
			   'attribute_set_id'   => 'e.attribute_set_id',
			   'type_id'            => 'e.type_id',
			   'sku'                => 'e.sku',
			   'has_options'        => 'e.has_options',
			   'required_options'   => 'e.required_options',
			   'created_at'         => 'e.created_at',
			   'updated_at'         => 'e.updated_at',
			   'cat_index_position' => 'cat_index.position',
			   'is_salable'         => $newIsSalable,
			   'price'              => new \Zend_Db_Expr(
				   "IFNULL(mageworx_price_index.price,price_index.price)"
			   ),
			   'tax_class_id'       => 'price_index.tax_class_id',
			   'final_price'        => new \Zend_Db_Expr(
				   "IFNULL(mageworx_price_index.final_price, price_index.final_price)"
			   ),
			   'minimal_price'      => new \Zend_Db_Expr($newPrice),
			   'min_price'          => new \Zend_Db_Expr(
				   "IFNULL(mageworx_price_index.min_price, price_index.min_price)"
			   ),
			   'max_price'          => new \Zend_Db_Expr(
				   "IFNULL(mageworx_price_index.max_price,price_index.max_price)"
			   ),
			   'tier_price'         => 'price_index.tier_price'
			];
            $select->reset(\Zend_Db_Select::COLUMNS)->columns($modifiedColumns);
			/**
			 * 2019-12-10 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
			 * «images are not loading on the when you log into your account online,
			 * it loads fine before you log in, it is when you log in after they disappear.»:
			 * https://github.com/royalwholesalecandy/core/issues/21
			 */
            $collection->addAttributeToSelect(array_diff(
            	$this->catalogConfig->getProductAttributes(), array_keys($modifiedColumns)
			));
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getIsSalable()
    {

        if (!$this->moduleManager->isEnabled('Magento_InventoryCatalog')) {
            return 'stock_status_index.stock_status';
        }

        $stockId             = $this->objectManager->get('Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite')
                                                   ->execute();
        $stockIndexTableName = $this->objectManager->get(
            'Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface'
        )->execute($stockId);

        $defaultStockProvider = $this->objectManager->get(
            'Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface'
        );

        if ($this->helperCalculate->checkModuleVersion('1.0.0', '1.0.3', '>=', '<=', 'Magento_InventoryCatalog')
            && $stockId === $defaultStockProvider->getId()
            && $stockIndexTableName === $this->resource->getTableName('inventory_stock_' . $stockId)) {
            return 'stock_status_index.is_salable';
        }

        if ($this->helperCalculate->checkModuleVersion('1.0.0', '1.0.3', '>=', '<=', 'Magento_InventoryCatalog')
            && ($stockId !== $defaultStockProvider->getId()
                || $stockIndexTableName !== $this->resource->getTableName('inventory_stock_' . $stockId))) {
            return 'stock_status_index.stock_status';
        }

        if ($this->helperCalculate->checkModuleVersion('1.0.4', '', '>=', '', 'Magento_InventoryCatalog')) {
            return $stockId === $defaultStockProvider->getId()
                ? 'stock_status_index.stock_status' : 'stock_status_index.is_salable';
        }

        return 'stock_status_index.stock_status';
    }
}