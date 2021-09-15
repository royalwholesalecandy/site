<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order;


class Reception extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_purchase_order_reception', 'por_id');
    }

    public function updateReceivedQty($receptionId)
    {

        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('bms_purchase_order_reception_item'), array(new \Zend_Db_Expr('SUM(pori_qty) as total')))
            ->where('pori_por_id = '.$receptionId);
        $result = $connection->fetchOne($select);

        $data['por_product_count'] = $result;
        $condition = 'por_id = '.$receptionId;
        $connection->update($this->getMainTable(), $data, $condition);

        return $this;
    }
}
