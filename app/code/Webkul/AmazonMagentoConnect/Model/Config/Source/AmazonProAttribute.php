<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model\Config\Source;

class AmazonProAttribute
{
    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        $optionArray = [
            'sku'                   => __('Sku'),
            'price'                 => __('Price'),
            'qty'                   => __('qty'),
            'type'                  => __('Product Type'),
            'value'                 => __('Product Value'),
            'Title'                 => __('Title'),
            'Brand'                 => __('Brand'),
            'Manufacturer'          => __('Manufacturer'),
            'MfrPartNumber'         => __('MfrPartNumber'),
            // 'item-condition'        => __('Condition'),
            // 'msrf'                  => __('MSRF'),
            'description'           => __('Description'),
            // 'bulletPoint'           => __('BulletPoint'),
            // 'item_height'           => __('Item Height'),
            // 'item_weight'           => __('Item Weight'),
            // 'item_length'           => __('Item Length'),
            // 'item_width'            => __('Item Width'),
            // 'package_height'        => __('Package Height'),
            // 'package_weight'        => __('Package Weight'),
            // 'package_length'        => __('Package Length'),
            // 'package_width'         => __('Package Width'),
            // 'item_type'             => __('ItemType'),
            // 'product_tax_code'      => __('Product Tax Code'),
            // 'condition'             => __('Condition'),
        ];
        asort($optionArray);
        return $optionArray;
    }
}
