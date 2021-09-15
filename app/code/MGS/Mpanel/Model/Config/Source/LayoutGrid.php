<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Mpanel\Model\Config\Source;

class LayoutGrid implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'productv1', 'label' => __('Product Version 1')], 
			['value' => 'productv2', 'label' => __('Product Version 2')], 
			['value' => 'productv3', 'label' => __('Product Version 3')], 
			
		];
    }
}
