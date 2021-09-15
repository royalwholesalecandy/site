<?php

namespace BoostMyShop\Supplier\Model\Order;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const draft = 'draft';
    const toApprove = 'to_approve';
    const toConfirm = 'to_confirm';
    const expected = 'expected';
    const complete = 'complete';
    const canceled = 'canceled';

    public function getStatuses($appendEmpty = true)
    {
        $options = array();

        if ($appendEmpty)
            $options[] = array('value' => '', 'label' => __('--Please Select--'));

        $options[] = array('value' => 'draft', 'label' => __('Draft'));

        $options[] = array('value' => 'to_approve', 'label' => __('Pending internal approval'));
        $options[] = array('value' => 'to_confirm', 'label' => __('Pending supplier confirmation'));

        $options[] = array('value' => 'expected', 'label' => __('Expected'));

        $options[] = array('value' => 'complete', 'label' => __('Complete'));
        $options[] = array('value' => 'canceled', 'label' => __('Canceled'));

        return $options;
    }

    public static function getOpenedStatuses()
    {
        $statuses = array();

        $statuses[] = self::expected;

        return $statuses;
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
