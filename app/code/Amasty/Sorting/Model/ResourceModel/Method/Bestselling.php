<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
namespace Amasty\Sorting\Model\ResourceModel\Method;

use Amasty\Sorting\Api\IndexedMethodInterface;
use Magento\Reports\Model\ResourceModel\Product\Index\AbstractIndex;

/**
 * Class Bestselling
 *
 * @package Amasty\Sorting\Model\Method
 */
class Bestselling extends AbstractIndexMethod
{
    /**
     * @var bool
     */
    protected $isAllGrouped = false;

    /**
     * Ignored product types list
     *
     * @var array
     */
    private $ignoredProductTypes = [];

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * Bestselling constructor.
     *
     * @param Context                                                         $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param string                                                          $connectionName
     * @param string                                                          $methodCode
     * @param string                                                          $methodName
     * @param array                                                           $ignoredProductTypes
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        $connectionName = null,
        $methodCode = '',
        $methodName = '',
        $ignoredProductTypes = []
    ) {
        parent::__construct($context, $connectionName, $methodCode, $methodName);
        $this->ignoredProductTypes = $ignoredProductTypes;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexTableName()
    {
        return 'amasty_sorting_bestsellers';
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        $storeLabel = $this->helper->getScopeValue('bestsellers/label', $store);
        if ($storeLabel) {
            return $storeLabel;
        }

        return parent::getMethodLabel($store);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortingColumnName()
    {
        return 'qty_ordered';
    }

    /**
     * {@inheritdoc}
     */
    public function doReindex()
    {
        $connection = $this->getConnection();

        $select = $connection->select();

        $select->group(['source_table.store_id', 'order_item.product_id']);

        $columns = [
            'product_id' => 'order_item.product_id',
            'store_id' => 'source_table.store_id',
            $this->getSortingColumnName() => new \Zend_Db_Expr('SUM(order_item.qty_ordered)'),
        ];

        $select->from(
            ['source_table' => $this->getTable('sales_order')],
            $columns
        )->joinInner(
            ['order_item' => $this->getTable('sales_order_item')],
            'order_item.order_id = source_table.entity_id',
            []
        )->joinLeft(
            ['order_item_parent' => $this->getTable('sales_order_item')],
            'order_item.parent_item_id = order_item_parent.item_id',
            []
        );

        $this->addIgnoreProductTypes($select);
        $this->addIgnoreStatus($select);
        $this->addFromDate($select);

        $select->useStraightJoin();
        // important!

        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);

        if (!in_array(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE, $this->ignoredProductTypes)) {
            $this->calculateGrouped();
        }
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     *
     * @return bool
     */
    private function addIgnoreProductTypes($select)
    {
        if ($this->ignoredProductTypes) {
            $select->where(
                'order_item.product_type NOT IN(?)',
                $this->ignoredProductTypes
            );
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     *
     * @return bool
     */
    private function addIgnoreStatus($select)
    {
        $orderStatuses = $this->helper->getScopeValue('bestsellers/exclude');
        if ($orderStatuses) {
            $orderStatuses = explode(',', $orderStatuses);
            $select->where('source_table.status NOT IN(?)', $orderStatuses);
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     *
     * @return bool
     */
    private function addFromDate($select)
    {
        $period = (int)$this->helper->getScopeValue('bestsellers/best_period');
        if ($period) {
            $from = $this->date->date(
                \Magento\Framework\DB\Adapter\Pdo\Mysql::TIMESTAMP_FORMAT,
                $this->date->timestamp() - $period * 24 * 3600
            );
            $select->where('source_table.created_at >= ?', $from);
            return true;
        }

        return false;
    }

    /**
     * This calculation can be very slow, add Grouped product type to ignore for improve speed
     * Count grouped products ordered qty
     * Sum of all simple qty which grouped by parent product and store
     */
    private function calculateGrouped()
    {
        $collection = $this->orderItemCollectionFactory->create();
        $collection->addFieldToFilter('product_type', \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE);
        $select = $collection->getSelect();
        $select->joinLeft(
            ['source_table' => $this->getTable('sales_order')],
            'main_table.order_id = source_table.entity_id',
            []
        );

        $this->addIgnoreStatus($select);
        $this->addFromDate($select);

        $result = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($collection->getItems() as $item) {
            $config = $item->getProductOptionByCode('super_product_config');
            $groupedId = $config['product_id'];
            $storeId = $item->getStoreId();

            if (!isset($result[$storeId][$groupedId])) {
                $result[$storeId][$groupedId] = 0;
            }
            // Sum of all simple qty which grouped by parent product
            $result[$storeId][$groupedId] += $item->getQtyOrdered();
        }

        if (!count($result)) {
            return;
        }

        $insert = [];
        foreach ($result as $storeId => $itemCounts) {
            foreach ($itemCounts as $productId => $count) {
                $insert[] = [
                    'product_id'                  => $productId,
                    'store_id'                    => $storeId,
                    $this->getSortingColumnName() => $count,
                ];
            }
        }

        $columns = ['product_id', 'store_id', $this->getSortingColumnName()];

        $this->getConnection()->insertArray($this->getMainTable(), $columns, $insert);
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $currDir)
    {
        $attributeCode = $this->helper->getScopeValue('bestsellers/best_attr');
        if ($attributeCode) {
            $collection->addAttributeToSort($attributeCode, $currDir);
        }
        return parent::apply($collection, $currDir);
    }
}
