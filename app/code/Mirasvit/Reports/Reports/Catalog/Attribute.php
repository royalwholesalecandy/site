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
use Mirasvit\ReportApi\Api\SchemaInterface;
use Mirasvit\Reports\Service\Pills\ChildItemPill;

class Attribute extends AbstractReport
{
    /**
     * @var ChildItemPill
     */
    private $pill;

    public function __construct(ChildItemPill $pill, Context $context)
    {
        $this->pill = $pill;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Sales by Attribute');
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

        $this->setInternalColumns(['catalog_product_entity|entity_id']);

        $this->setColumns([
            'sales_order_item|qty_ordered__sum',
            'sales_order_item|tax_amount__sum',
            'sales_order_item|discount_amount__sum',
            'sales_order_item|amount_refunded__sum',
            'sales_order_item|row_total__sum',
        ]);

        $this->setDimensions([
            'catalog_product_entity|status',
        ]);

        $this->setPrimaryDimensions($this->context->getProvider()->getSimpleColumns('catalog_product_entity'));

        $this->getGridConfig()
            ->disablePagination();

        $this->getChartConfig()
            ->setDefaultColumns([
                'sales_order_item|row_total__sum',
            ])
            ->setType('pie');

        //        // show only visible attributes
        //        if ($dimension = $this->context->getRequest()->getParam('dimension')) {
        //            $this->setInternalFilters([
        //                [$this->context->getRequest()->getParam('dimension'), true, 'notnull'],
        //            ]);
        //        }
        //
        //        $attributeCode = substr(
        //            $this->context->getRequest()->getParam('dimension'),
        //            strpos($this->context->getRequest()->getParam('dimension'), '|') + 1
        //        );
        //
        //        if ($this->pill->isAttributeApplicable($attributeCode)) {
        //            // delete tables' relation in order to connect tables through the temporary table
        //            $this->removeRelation($this->context->getProvider(), 'catalog_product_entity', 'sales_order_item');
        //            $this->removeRelation($this->context->getProvider(), 'sales_order', 'sales_order_item');
        //        }
    }

    /**
     * Remove relations for tables.
     * @param SchemaInterface $schema
     * @param string          $table1
     * @param string          $table2
     */
    private function removeRelation(SchemaInterface $schema, $table1, $table2)
    {
        $relations = $schema->getRelations();
        $counter   = 0;
        foreach ($relations as $key => $relation) {
            if (in_array($relation->getLeftTable()->getName(), [$table1, $table2], true)
                && in_array($relation->getRightTable()->getName(), [$table1, $table2], true)
                && $relation->getLeftTable()->getName() !== $relation->getRightTable()->getName()
            ) {
                if (!$relation->getRightField() || !$relation->getLeftField()) {
                    unset($relations[$key]);
                }
            }
        }

        $schema->setRelations($relations);
    }
}
