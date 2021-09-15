<?php

namespace BoostMyShop\AdvancedStock\Model\Routing\Store;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    const alwaysBestPriority = 1;
    const withStockOrderByPriority = 2;


    public function getModes($appendEmpty = true)
    {
        $options = array();

        if ($appendEmpty)
            $options[] = array('value' => '', 'label' => __('--Please Select--'));

        $options[] = array('value' => self::alwaysBestPriority, 'label' => __('Always priority one'));
        $options[] = array('value' => self::withStockOrderByPriority, 'label' => __('Warehouse with stock, order by priority'));

        return $options;
    }

    public function toOptionArray()
    {
        $options = array();

        foreach($this->getModes(false) as $item)
        {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

}
