<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Method;

/**
 * Class Saving
 */
class Saving extends AbstractMethod
{
    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        $storeLabel = $this->helper->getScopeValue('biggest_saving/label', $store);
        if ($storeLabel) {
            return $storeLabel;
        }

        return parent::getMethodLabel($store);
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $direction)
    {
        $connection = $this->getConnection();
        $table      = $this->getPriceAlias($collection);

        /** LEAST(min_price, tier_price) */
        $least = $connection->getLeastSql(["$table.min_price", "$table.tier_price"]);
        $price = $table . '.price';
        /** tier_price IS NOT NULL */
        $tpNotNull = $connection->prepareSqlCondition("$table.tier_price", ['notnull' => true]);
        /** IF (tier_price IS NOT NULL, LEAST(min_price, tier_price), min_price) */
        $minPrice = $connection->getCheckSql($tpNotNull, $least, "$table.min_price");

        if ($this->helper->getScopeValue('biggest_saving/saving')) {
            $percent = "($price - $minPrice) * 100 / $price";
            $saving  = $connection->getCheckSql($price, $percent, 0);
        } else {
            $saving = "($price - $minPrice)";
        }

        $expr = new \Zend_Db_Expr($saving . ' ' . $direction);

        $collection->getSelect()->order($expr);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return string
     */
    private function getPriceAlias($collection)
    {
        $tableAliases = array_keys($collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM));
        if (in_array($collection::INDEX_TABLE_ALIAS, $tableAliases)) {
            return $collection::INDEX_TABLE_ALIAS;
        }

        return reset($tableAliases);
    }
}
