<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model\Config\Source;

/**
 * Class Qwc
 * @package Magenest\QuickBooksDesktop\Model\Config\Source
 */
class Qwc implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '4', 'label' => __('Query Company')],
            ['value' => '1', 'label' => __('Synchronization from Magento') ],
            ['value' => '8', 'label' => __('Tax Query') ],
            // ['value' => '2', 'label' => __('Mapping Customer') ],
            // ['value' => '3', 'label' => __('Mapping Product') ],
//            ['value' => '7', 'label' => __('Query Price Level List') ],
//            ['value' => '5', 'label' => __('Query Invoice') ],
//            ['value' => '6', 'label' => __('Query SalesOrder') ]
//            ,
        ];
    }
}
