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



namespace Mirasvit\Reports\Reports\Catalog;

use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Processor\ResponseItem;
use Mirasvit\Reports\Model\Config;

class Product extends AbstractReport
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Product Performance');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('catalog_product_entity');

        $this->setPrimaryFilters([
            'sales_order|created_at',
            'sales_order|store_id',
        ]);

        $this->setInternalColumns([
            'catalog_product_entity|sku',
            'catalog_product_entity|entity_id',
        ]);

        $this->setColumns([
            'catalog_product_entity|name',
            'sales_order|entity_id__cnt',
            'sales_order_item|qty_ordered__sum',
            'sales_order_item|tax_amount__sum',
            'sales_order_item|discount_amount__sum',
            'sales_order_item|amount_refunded__sum',
            'sales_order_item|gross_margin__avg',
            'sales_order_item|row_total__sum',
        ]);

        $this->setPrimaryDimensions([
            'catalog_product_entity|sku',
        ])->setDimensions([
            'catalog_product_entity|sku',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActions(ResponseItem $item, RequestInterface $request = null)
    {
        $sku = $item->getData('catalog_product_entity|sku');
        $id  = $item->getData('catalog_product_entity|entity_id');

        $salesParams = [
            'catalog_product_entity|sku' => $sku,
        ];

        //        $dateFilter = $this->getUiContext()->context->getFilterParam('sales_order|created_at');
        //        if (is_array($dateFilter) && isset($dateFilter['from'], $dateFilter['to'])) {
        //            // key corresponds to the fast filter of the 'catalog_product_detail' report
        //            $salesParams['sales_order_item|created_at__day'] = [
        //                'from' => $dateFilter['from'],
        //                'to'   => $dateFilter['to'],
        //            ];
        //        }

        return [
            [
                'label' => __('View Product'),
                'href'  => $this->context->urlManager->getUrl('catalog/product/edit', ['id' => $id]),
            ],
            [
                'label' => __('View Sales'),
                'href'  => $this->getReportUrl('catalog_product_detail', $salesParams),
            ],
        ];
    }
}