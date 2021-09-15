<?php

namespace BoostMyShop\Supplier\Model\Source\OrderProduct;

class PriceImport
{


    public function toOptionArray()
    {
        $options = array();

        $options[] = array('value' => 'product_cost', 'label' => 'Use product cost attribute');
        $options[] = array('value' => 'product_supplier_association', 'label' => 'Use supplier / product association price');
        $options[] = array('value' => 'empty', 'label' => 'Leave empty');

        return $options;
    }
}
