<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class Warehouses implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [1 => 'Default'];
    }

}
