<?php

namespace BoostMyShop\Supplier\Model\Source;

class Warehouse
{

    public function toOptionArray()
    {
        $options = array();

        $options[] = array('value' => 1, 'label' => 'Default');

        return $options;
    }
}
