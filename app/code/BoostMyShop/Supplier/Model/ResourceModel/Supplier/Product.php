<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Supplier;


class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_supplier_product', 'sp_id');
    }

    public function getIdFromProductSupplier($productId, $supplierId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable(),array('sp_id'))
            ->where('sp_product_id = ' .$productId.' and sp_sup_id = '.$supplierId);

        return $connection->fetchOne($select);

    }

    public function removeOtherPrimary($productId, $supplierId)
    {
        $this->getConnection()->update($this->getMainTable(), ['sp_primary' => 0], 'sp_product_id='.$productId.' and sp_sup_id <> '.$supplierId);
        return $this;
    }


    public function updateLastBuyingPrice($spId, $productId, $supplierId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('bms_purchase_order'))
            ->join($this->getTable('bms_purchase_order_product'), '(po_id = pop_po_id)', ['*'])
            ->join($this->getTable('bms_purchase_order_reception'), '(po_id = por_po_id)', [])
            ->join($this->getTable('bms_purchase_order_reception_item'), '(por_id = pori_por_id)', [])
            ->where('pop_product_id = ' .$productId.' and po_sup_id = '.$supplierId)
            ->order('por_created_at desc')
            ->limit(0, 1);

        $row = $connection->fetchRow($select);

        if ($row)
        {
            $data = [
                    'sp_last_buying_price' => ($row['pop_price'] / $row['pop_qty_pack'] * (1 - $row['pop_discount_percent'] / 100)),
                    'sp_last_buying_price_base' => ($row['pop_price_base'] / $row['pop_qty_pack'] * (1 - $row['pop_discount_percent'] / 100)),
                    ];
            $this->getConnection()->update($this->getMainTable(), $data, 'sp_id = '.$spId);
        }
    }

}
