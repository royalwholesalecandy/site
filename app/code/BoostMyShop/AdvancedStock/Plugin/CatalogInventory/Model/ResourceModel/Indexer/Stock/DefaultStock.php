<?php

namespace BoostMyShop\AdvancedStock\Plugin\CatalogInventory\Model\ResourceModel\Indexer\Stock;

class DefaultStock extends \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
{
    private $queryProcessorComposite;

    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $qtyExpr = $connection->getCheckSql('cisi.qty > 0', 'cisi.qty', 0);
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );

        //BMS: invert columns website_id & stock_id to match to columns order in table  cataloginventory_stock_status_idx
        //BMS : remove restriction on getDefaultScopeId
        $select
            ->join(
                ['cisi' => $this->getTable('cataloginventory_stock_item')],
                'cisi.product_id = e.entity_id',
                ['website_id'])
            ->joinInner(
                ['cis' => $this->getTable('cataloginventory_stock')],
                'cisi.stock_id = cis.stock_id',
                ['stock_id'])
            ->columns(
                ['qty' => $qtyExpr]
        )
        //    ->where(
        //    'cis.website_id = ?',
        //    $this->getStockConfiguration()->getDefaultScopeId()
        //)
            ->where('e.type_id = ?', $this->getTypeId())
            ->group(['e.entity_id', 'cisi.website_id', 'cis.stock_id']);

        $select->columns(['status' => $this->getStatusExpression($connection, true)]);
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

    //required as it is a private method in parent...
    private function getQueryProcessorComposite()
    {
        if (null === $this->queryProcessorComposite) {
            $this->queryProcessorComposite = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorComposite');
        }
        return $this->queryProcessorComposite;
    }


}