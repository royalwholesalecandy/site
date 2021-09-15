<?php

namespace Boolfly\PaymentFee\Block\Adminhtml\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class Totals extends Template
{

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();

        if(!$this->getSource()->getFeeAmount() || $this->getSource()->getFeeAmount() == 0) {
            return $this;
        }
        $total = new DataObject(
            [
                'code' => 'fee',
                'value' => $this->getSource()->getFeeAmount(),
                'label' => __('Credit Card Fee'),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}