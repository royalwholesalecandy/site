<?php

namespace BoostMyShop\AdvancedStock\Plugin\Grouped\Model\ResourceModel\Indexer\Stock;


class Grouped extends \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
{
    private $queryProcessorComposite;

    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $select = parent::_getStockStatusSelect($entityIds, $usePrimaryTable);
        $select->reset(
            \Magento\Framework\DB\Select::COLUMNS
            )->columns(
                ['e.entity_id', 'cis.website_id', 'cis.stock_id']
            )->joinLeft(
                ['l' => $this->getTable('catalog_product_link')],
                'e.' . $metadata->getLinkField() . ' = l.product_id AND l.link_type_id=' .
                \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
                []
            )->joinLeft(
                ['le' => $this->getTable('catalog_product_entity')],
                'le.entity_id = l.linked_product_id',
                []
            )->joinLeft(
                ['i' => $idxTable],
                'i.product_id = l.linked_product_id AND cis.website_id = i.website_id AND cis.stock_id = i.stock_id',
                []
            )->columns(
                ['qty' => new \Zend_Db_Expr('0')]
            );

        $statusExpression = $this->getStatusExpression($connection);

        $optExpr = $connection->getCheckSql("le.required_options = 0", 'i.stock_status', 0);
        $stockStatusExpr = $connection->getLeastSql(["MAX({$optExpr})", "MIN({$statusExpression})"]);

        $select->columns(['status' => $stockStatusExpr]);

        //BMS customization
        $select->reset(\Magento\Framework\DB\Select::WHERE);
        $select->where("(e.type_id = 'grouped')");

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    protected function _updateIndex($entityIds)
    {
        $connection = $this->getConnection();
        $select = $this->_getStockStatusSelect($entityIds, true);
        $select = $this->getQueryProcessorComposite()->processQuery($select, $entityIds, true);
        $query = $connection->query($select);

        $i = 0;
        $data = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $i++;
            $data[] = [
                'product_id' => (int)$row['entity_id'],
                'website_id' => (int)$row['website_id'],
                'stock_id' => (int)$row['stock_id'],    //BMS Changes
                'qty' => (double)$row['qty'],
                'stock_status' => (int)$row['status'],
            ];

            if ($i % 1000 == 0) {
                $this->_updateIndexTable($data);
                $data = [];
            }
        }
        $this->_updateIndexTable($data);

        return $this;
    }

    private function getQueryProcessorComposite()
    {
        if (null === $this->queryProcessorComposite) {
            $this->queryProcessorComposite = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorComposite');
        }
        return $this->queryProcessorComposite;
    }

}