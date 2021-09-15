<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order;


class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('bms_purchase_order_product', 'pop_id');
    }

    /**
     * Update received quantity for one product in one PO
     *
     * @param $orderId
     * @param $productId
     * @return $this
     */
    public function updateReceivedQuantity($orderId, $productId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getTable('bms_purchase_order_reception_item'), array(new \Zend_Db_Expr('SUM(pori_qty) as total')))
            ->join($this->getTable('bms_purchase_order_reception'), 'por_id = pori_por_id')
            ->where('por_po_id = '.$orderId)
            ->where('pori_product_id = '.$productId);
        $result = $connection->fetchOne($select);


        $data['pop_qty_received'] = $result;
        $condition = 'pop_product_id = '.$productId.' and pop_po_id='.$orderId;
        $connection->update($this->getMainTable(), $data, $condition);

        return $this;
    }

    /**
     *
     * Get quantity to receive for a product, for every opened PO
     * @param $productId
     * @param int $storeId
     * @return int
     */
    public function getQuantityToReceive($productId, $storeId = 0)
    {
        $connection = $this->getConnection();

        $statuses = \BoostMyShop\Supplier\Model\Order\Status::getOpenedStatuses();
        foreach($statuses as $k => $v)
        {
            $statuses[$k] = "'".$v."'";
        }

        $select = $connection
            ->select()
            ->from($this->getTable('bms_purchase_order_product'), array(new \Zend_Db_Expr('SUM(pop_qty * pop_qty_pack - pop_qty_received * pop_qty_pack) as total')))
            ->join($this->getTable('bms_purchase_order'), 'po_id = pop_po_id', [])
            ->where('po_status in ('.implode(',', $statuses).')')
            ->where('po_type = "po"')
            ->where('pop_product_id = '.$productId);

        $result = $connection->fetchOne($select);

        return ($result > 0 ? $result : 0);
    }

}
