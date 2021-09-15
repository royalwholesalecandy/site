<?php declare(strict_types=1);

namespace Boolfly\PaymentFee\Block\Adminhtml\Sales\Order\Invoice;

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

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
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