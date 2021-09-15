<?php

namespace BoostMyShop\Supplier\Model\Invoice;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const paid = 'paid';
    const pending = 'pending';
    const partially_paid = 'partially_paid';
    const canceled = 'canceled';


    public function getStatuses($appendEmpty = true)
    {
        $options = array();

        if ($appendEmpty)
            $options[] = array('value' => '', 'label' => __('--Please Select--'));

        $options[] = array('value' => 'pending', 'label' => __('Pending'));
        $options[] = array('value' => 'partially_paid', 'label' => __('Partially paid'));
        $options[] = array('value' => 'paid', 'label' => __('Paid'));
        $options[] = array('value' => 'canceled', 'label' => __('Canceled'));


        return $options;
    }


    public function toOptionArray()
    {
        $options = array();

        foreach($this->getStatuses(false) as $item)
        {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

}
