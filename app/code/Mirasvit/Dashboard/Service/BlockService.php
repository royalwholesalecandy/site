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
 * @package   mirasvit/module-dashboard
 * @version   1.2.35
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Service;

use Magento\Framework\Stdlib\ArrayManager;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Report\Api\Service\DateServiceInterface;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;

class BlockService
{
    private $arrayManager;

    private $requestBuilder;

    private $dateService;

    private $schema;

    public function __construct(
        ArrayManager $arrayManager,
        RequestBuilderInterface $requestBuilder,
        DateServiceInterface $dateService,
        SchemaInterface $schema
    ) {
        $this->arrayManager   = $arrayManager;
        $this->requestBuilder = $requestBuilder;
        $this->dateService    = $dateService;
        $this->schema         = $schema;
    }

    public function getApiResponse(BlockInterface $block, array $filters)
    {
        $renderer = $block->getConfig()->getRenderer();

        $filters = array_merge($filters, $block->getConfig()->getFilters());

        $rTable      = null;
        $rColumns    = [];
        $rDimensions = [];
        $rFilters    = [];
        $dateColumn  = null;
        $rPageSize   = 1000;

        $request = $this->requestBuilder->create();

        if ($renderer === 'table') {
            $dimensions = $block->getConfig()->getTable()->getDimensions();
            if (count($dimensions) == 0) {
                return null;
            }

            $rDimensions = $dimensions;

            $dimensionColumn = $this->schema->getColumn($dimensions[0]);

            if (!$dimensionColumn) {
                return null;
            }

            $rTable = $dimensionColumn->getTable()->getName();

            $rColumns = array_merge($rDimensions, $block->getConfig()->getTable()->getColumns());

            $rPageSize = $block->getConfig()->getTable()->getPageSize();
            $orders    = $block->getConfig()->getTable()->getSortOrders();
            foreach ($orders as $item) {
                $request->addSortOrder($item['column'], $item['direction']);
            }

            $dateColumn = $this->getDateColumn($rTable);
        } elseif ($renderer === 'chart') {
            $dimension = $block->getConfig()->getChart()->getDimension();

            $rDimensions = [$dimension];

            $dimensionColumn = $this->schema->getColumn($dimension);

            if (!$dimensionColumn) {
                return null;
            }

            $rTable = $dimensionColumn->getTable()->getName();

            $rColumns = array_merge($rDimensions, $block->getConfig()->getChart()->getColumns());

            $dateColumn = $this->getDateColumn($rTable);
        } else {
            $identifier = $block->getConfig()->getSingle()->getColumn();
            $column     = $this->schema->getColumn($identifier);

            if (!$column) {
                return null;
            }

            $rTable = $column->getTable()->getName();

            $rColumns[] = $column->getIdentifier();

            $dateColumn = $this->getDateColumn($rTable);

            if ($dateColumn) {
                $dimensionColumn = $this->schema->getColumn("$rTable|created_at__day");

                if ($dimensionColumn) {
                    $rColumns[]    = $dimensionColumn->getIdentifier();
                    $rDimensions[] = $dimensionColumn->getIdentifier();
                }
            }
        }

        if ($dateColumn) {
            $from = null;
            $to   = null;
            foreach ($filters as $filter) {
                if ($filter['column'] === 'DATE') {
                    $filter['column'] = $dateColumn->getIdentifier();

                    $filter['group'] = 'A';

                    if ($block->getConfig()->getDateRange()->isOverride()) {
                        $range = $this->dateService->getInterval(
                            $block->getConfig()->getDateRange()->getRange()
                        );

                        if ($filter['condition_type'] == 'gteq') {
                            $filter['value'] = $range->getFrom()->toString(\Zend_Date::W3C);
                        }

                        if ($filter['condition_type'] == 'lteq') {
                            $filter['value'] = $range->getTo()->toString(\Zend_Date::W3C);
                        }
                    }

                    if ($filter['condition_type'] == 'gteq') {
                        $from = $filter['value'];
                    }

                    if ($filter['condition_type'] == 'lteq') {
                        $to = $filter['value'];
                    }
                }

                $rFilters[] = $filter;
            }

            if ($block->getConfig()->getSingle()->getCompare()) {
                $previous = $this->dateService->getPreviousInterval(new \Magento\Framework\DataObject([
                    'from' => new \Zend_Date($from),
                    'to'   => new \Zend_Date($to),
                ]), $block->getConfig()->getSingle()->getCompare());

                $rFilters[] = [
                    'column'         => $dateColumn->getIdentifier(),
                    'condition_type' => 'gteq',
                    'value'          => $previous->getFrom()->toString(\Zend_Date::W3C),
                    'group'          => 'C',
                ];

                $rFilters[] = [
                    'column'         => $dateColumn->getIdentifier(),
                    'condition_type' => 'lteq',
                    'value'          => $previous->getTo()->toString(\Zend_Date::W3C),
                    'group'          => 'C',
                ];
            }

            if ($block->getConfig()->getChart()->getCompare()) {
                $previous = $this->dateService->getPreviousInterval(new \Magento\Framework\DataObject([
                    'from' => new \Zend_Date($from),
                    'to'   => new \Zend_Date($to),
                ]), $block->getConfig()->getChart()->getCompare());

                $rFilters[] = [
                    'column'         => $dateColumn->getIdentifier(),
                    'condition_type' => 'gteq',
                    'value'          => $previous->getFrom()->toString(\Zend_Date::W3C),
                    'group'          => 'C',
                ];

                $rFilters[] = [
                    'column'         => $dateColumn->getIdentifier(),
                    'condition_type' => 'lteq',
                    'value'          => $previous->getTo()->toString(\Zend_Date::W3C),
                    'group'          => 'C',
                ];
            }
        }

        $request
            ->setTable($this->getTable($block))
            ->setColumns($rColumns)
            ->setDimensions($rDimensions)
            ->setPageSize($rPageSize);

        foreach ($rFilters as $filter) {
            $request->addFilter(
                $filter['column'],
                $filter['value'],
                $filter['condition_type'],
                isset($filter['group']) ? $filter['group'] : ''
            );
        }

        return $request->process();
    }

    private function getTable(BlockInterface $block)
    {
        if ($block->getConfig()->getRenderer() === 'single') {
            $column = $this->schema->getColumn(
                $block->getConfig()->getSingle()->getColumn()
            );

            if ($column) {
                return $column->getTable()->getName();
            }
        }

        if ($block->getConfig()->getRenderer() === 'table') {
            $dimensions = $block->getConfig()->getTable()->getDimensions();
            if (count($dimensions) > 0) {
                $column = $this->schema->getColumn($dimensions[0]);

                if ($column) {
                    return $column->getTable()->getName();
                }
            }
        }

        if ($block->getConfig()->getRenderer() === 'chart') {
            $dimension = $block->getConfig()->getChart()->getDimension();
            $column    = $this->schema->getColumn($dimension);

            if ($column) {
                return $column->getTable()->getName();
            }
        }

        return false;
    }

    /**
     * @param string $tableIdentifier
     * @return \Mirasvit\ReportApi\Api\Config\ColumnInterface|null
     */
    private function getDateColumn($tableIdentifier)
    {
        $column = $this->schema->getColumn("$tableIdentifier|created_at");

        return $column;
    }
}