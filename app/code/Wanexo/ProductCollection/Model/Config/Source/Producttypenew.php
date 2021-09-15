<?php
namespace Wanexo\ProductCollection\Model\Config\Source;

class Producttypenew implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
            [
                'value' =>    1,
                'label' => __('New Products')
            ],
			[
                'value' =>    2,
                'label' => __('Products From Category ID')
            ],
        ];
    }
}
