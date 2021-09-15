<?php

namespace BoostMyShop\AdvancedStock\Model\Warehouse;


class FullfilmentMethod implements \Magento\Framework\Option\ArrayInterface
{

    const METHOD_NONE = 99;
    const METHOD_SHIPPING = 1;
    const METHOD_PICKUP = 2;
    const METHOD_DROP_SHIP = 3;

    public function toOptionArray()
    {
        $options = array();

        $options[self::METHOD_SHIPPING] = __('Shipping');
        $options[self::METHOD_PICKUP] = __('Pickup');
        $options[self::METHOD_DROP_SHIP] = __('Dropship');
        $options[self::METHOD_NONE] = __('None');

        return $options;
    }

}