<?php

namespace BoostMyShop\Supplier\Model\Invoice;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    const invoice = 'invoice';
    const creditMemo = 'creditmemo';

    public function toOptionArray()
    {
        $options = array();

        $options['invoice'] = __('Invoice');
        $options['creditmemo'] = __('Creditmemo');

        return $options;
    }

}
