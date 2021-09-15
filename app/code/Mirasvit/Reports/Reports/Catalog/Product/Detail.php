<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.31
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Reports\Catalog\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\Reports\Model\Config;

class Detail extends AbstractReport
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Config $config,
        Context $context
    ) {
        $this->productRepository = $productRepository;
        $this->config            = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if ($this->getProductSku()) {
            $product = $this->productRepository->get($this->getProductSku());

            $name = $product->getName();

            return __('Product Performance / %1', $name);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('sales_order_item');

        $this->setPrimaryFilters([
            'sales_order_item|created_at__day',
            'sales_order_item|store_id',
        ]);

        $this->setColumns([
            'sales_order|entity_id__cnt',
            'sales_order_item|qty_ordered__sum',
            'sales_order_item|tax_amount__sum',
            'sales_order_item|discount_amount__sum',
            'sales_order_item|amount_refunded__sum',
            'sales_order_item|row_total__sum',
        ]);

        $this->setDimensions([
            'sales_order_item|created_at__day',
        ]);

        $this->setPrimaryDimensions([
            'sales_order_item|created_at__day',
            'sales_order_item|created_at__week',
            'sales_order_item|created_at__month',
            'sales_order_item|created_at__quarter',
            'sales_order_item|created_at__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'sales_order_item|qty_ordered__sum',
            ]);

        $this->setInternalFilters([
            [
                'column'        => 'catalog_product_entity|sku',
                'conditionType' => 'eq',
                'value'         => $this->getProductSku(),
            ],
        ]);
    }

    private function getProductSku()
    {
        $filters = $this->context->getRequest()->getParam('filters');

        if (is_array($filters) && isset($filters['catalog_product_entity|sku'])) {
            return $filters['catalog_product_entity|sku'];
        }

        return false;
    }
}
