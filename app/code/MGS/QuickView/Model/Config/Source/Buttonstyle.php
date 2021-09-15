<?php

namespace MGS\QuickView\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Buttonstyle implements ArrayInterface {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'v1',
                'label' => 'Version 1',
            ),
            array(
                'value' => 'v2',
                'label' => 'Version 2',
            )
        );
    }

}
