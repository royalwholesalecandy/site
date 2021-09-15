<?php
namespace Wanexo\ProductCollection\Model\Config\Source;

class Upsell implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
            [
                'value' =>    1,
				'label' => __('Upsell Product Only.')
            ],
			[
                'value' =>    2,
                'label' => __('Static block if Upsell prodcuts are not available.')
            ],
			[
                'value' =>    3,
                'label' => __('Upsell product and static block both.')
            ]
        ];
    }
}
