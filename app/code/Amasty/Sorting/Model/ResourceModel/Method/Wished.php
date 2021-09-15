<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Method;

/**
 * Class Wished
 */
class Wished extends AbstractIndexMethod
{
    /**
     * {@inheritdoc}
     */
    public function getIndexTableName()
    {
        return 'amasty_sorting_wished';
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        $storeLabel = $this->helper->getScopeValue('wished/label', $store);
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
        return 'wished';
    }

    /**
     * {@inheritdoc}
     */
    public function doReindex()
    {
        $connection = $this->getConnection();
        $select     = $connection->select();

        $select->group(['source_table.store_id', 'source_table.product_id']);

        $viewsNumExpr = new \Zend_Db_Expr('COUNT(source_table.wishlist_item_id)');

        $columns = [
            'product_id' => 'source_table.product_id',
            'store_id' => 'source_table.store_id',
            'wished' => $viewsNumExpr,
        ];

        $select->from(
            ['source_table' => $this->getTable('wishlist_item')],
            $columns
        );

        $this->addFromDate($select);

        $select->useStraightJoin();

        $insertQuery = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
        $connection->query($insertQuery);
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     *
     * @return bool
     */
    private function addFromDate($select)
    {
        $period = (int)$this->helper->getScopeValue('wished/wished_period');
        if ($period) {
            $from = $this->date->date(
                \Magento\Framework\DB\Adapter\Pdo\Mysql::TIMESTAMP_FORMAT,
                $this->date->timestamp() - $period * 24 * 3600
            );
            $select->where('source_table.added_at >= ?', $from);
            return true;
        }

        return false;
    }
}
