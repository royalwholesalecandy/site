<?php

namespace BoostMyShop\Supplier\Model\Order;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    const purchaseOrder = 'po';
    const dropShip = 'ds';

    public function toOptionArray()
    {
        $options = array();

        $options['po'] = __('Purchase order');
        $options['ds'] = __('Drop Shipping');

        return $options;
    }

}
