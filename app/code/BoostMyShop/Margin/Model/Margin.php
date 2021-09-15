<?php

namespace BoostMyShop\Margin\Model;

class Margin
{
    protected $_order;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    ){
        $this->_orderFactory = $orderFactory;
    }

    public function init($order)
    {
        if (is_numeric($order))
            $order = $this->_orderFactory->create()->load($order);
        $this->_order = $order;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function getItems()
    {
        return $this->_order->getAllVisibleItems();
    }

    public function getOrderItemCost($orderItem)
    {
        switch($orderItem->getproduct_type())
        {
            case 'bundle':
            case 'configurable':
                return $this->getTotalCostFromChildren($orderItem);
                break;
            default:
                return $orderItem->getbase_cost() * $orderItem->getqty_invoiced();
        }
    }

    public function getMarginValue($orderItem)
    {
        if ($orderItem->getrow_invoiced() > 0)
            return $orderItem->getrow_invoiced() - $orderItem->gettax_invoiced() - $this->getOrderItemCost($orderItem);
        else
            return 0;
    }

    public function getMarginPercent($orderItem)
    {
        if ($orderItem->getrow_invoiced() > 0)
            return (int)($this->getMarginValue($orderItem) / ($orderItem->getrow_invoiced() - $orderItem->gettax_invoiced()) * 100);

    }

    public function getOrderMarginValue()
    {
        $value = 0;
        foreach($this->getItems() as $orderItem)
            $value += $this->getMarginValue($orderItem);
        return $value;
    }

    public function getOrderMarginPercent()
    {
        if ($this->_order->getsubtotal_invoiced() > 0)
            return (int)($this->getOrderMarginValue() / $this->_order->getsubtotal_invoiced() * 100);

    }

    protected function getTotalCostFromChildren($parentOrderItem)
    {
        $totalCost = 0;

        foreach($this->_order->getAllItems() as $orderItem)
        {
            if ($orderItem->getparent_item_id() == $parentOrderItem->getId())
                $totalCost += $this->getOrderItemCost($orderItem);
        }

        return $totalCost;
    }

}