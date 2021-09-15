<?php declare(strict_types=1);

namespace Boolfly\PaymentFee\Block\Adminhtml\Sales\Order\Creditmemo;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

class Totals extends Template
{

    /**
     * Get data (totals) source model
     *
     * @return DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();

        if(!$this->getSource()->getFeeAmount() || $this->getSource()->getFeeAmount() == 0) {
            return $this;
        }
        $fee = new DataObject(
            [
                'code' => 'fee',
                'strong' => false,
                'value' => $this->getSource()->getFeeAmount(),
                'label' => __('Credit Card Fee'),
            ]
        );

        $this->getParentBlock()->addTotalBefore($fee, 'grand_total');

        return $this;
    }
}