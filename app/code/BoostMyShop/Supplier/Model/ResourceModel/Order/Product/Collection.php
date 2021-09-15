<?php

namespace BoostMyShop\Supplier\Model\ResourceModel\Order\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Supplier\Model\Order\Product', 'BoostMyShop\Supplier\Model\ResourceModel\Order\Product');
    }

    public function addOrderFilter($orderId)
    {

        $this->getSelect()->where("pop_po_id = ".$orderId);

        return $this;
    }

    public function addProductFilter($productId)
    {

        $this->getSelect()->where("pop_product_id = ".$productId);

        return $this;
    }

    public function getAlreadyAddedProductIds($orderId)
    {
        $this->getSelect()->reset()->from($this->getMainTable(), ['pop_product_id'])->where("pop_po_id = ".$orderId);
        $ids = $this->getConnection()->fetchCol($this->getSelect());

        return $ids;
    }

    public function getOrdersHistory($productId)
    {
        $this->getSelect()
            ->where("pop_product_id = ".$productId)
            ->join($this->getTable('bms_purchase_order'), 'pop_po_id = po_id')
            ->join($this->getTable('bms_supplier'), 'po_sup_id = sup_id');
        return $this;
    }

    public function addOrderStatusFilter($status)
    {
        $this->getSelect()
            ->join($this->getTable('bms_purchase_order'), 'pop_po_id = po_id')
            ->join($this->getTable('bms_supplier'), 'po_sup_id = sup_id')
            ->where("po_status = '".$status."'");

        return $this;
    }

    public function addExpectedFilter()
    {
        $this->getSelect()->where('pop_qty > pop_qty_received');
        return $this;
    }


    public function addRealEta()
    {
        $this->getSelect()->columns(new \Zend_Db_Expr('if(pop_eta, pop_eta, po_eta) as real_eta'));
        return $this;
    }

    public function addBuyingPriceWithDiscount()
    {
        $this->getSelect()->columns(new \Zend_Db_Expr('(pop_price_base - (pop_price_base / 100 * pop_discount_percent)) * (1 - po_global_discount / 100) as pop_price_with_discount_base'));
        return $this;
    }

    public function addBuyingPriceWithCosts()
    {
        $this->getSelect()->columns(new \Zend_Db_Expr('((pop_price_base - (pop_price_base / 100 * pop_discount_percent)) * (1 - po_global_discount / 100) + pop_extended_cost_base) as pop_price_with_cost_base'));
        return $this;
    }

    public function countToReceive()
    {
        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);

        $this->getSelect()->columns(new \Zend_Db_Expr('SUM(pop_qty * pop_qty_pack - pop_qty_received * pop_qty_pack)'));

        $result = $this->getConnection()->fetchOne($this->getSelect());
        if (!$result || ($result < 0))
            $result = 0;
        return $result;

    }

    public function addUnitPrices()
    {
        $this->getSelect()->columns(array(new \Zend_Db_Expr('(pop_price / pop_qty_pack) * (1 - po_global_discount / 100) as unit_price'),new \Zend_Db_Expr('(pop_price_base / pop_qty_pack) * (1 - po_global_discount / 100) as unit_price_base')));
        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch($field)
        {
            case 'unit_price':
                if(array_key_exists('from', $condition))
                    $this->_select->where('(pop_price / pop_qty_pack) >='.$condition['from']);

                if(array_key_exists('to', $condition))
                    $this->_select->where('(pop_price / pop_qty_pack) <='.$condition['to']);
                break;
            case 'unit_price_base':
                if(array_key_exists('from', $condition))
                    $this->_select->where('(pop_price_base / pop_qty_pack) >='.$condition['from']);

                if(array_key_exists('to', $condition))
                    $this->_select->where('(pop_price_base / pop_qty_pack) <='.$condition['to']);
                break;
            default:
                parent::addFieldToFilter($field, $condition);
                break;
        }

        return $this;
    }
}
