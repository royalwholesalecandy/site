<?php
namespace Wanexo\ProductCollection\Model\Config\Source;

class Itemview implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
            [
                'value' =>    1,
                'label' => __('Item view style 1')
            ],
			[
                'value' =>    2,
                'label' => __('Item view style 2')
            ],
			[
                'value' =>    3,
                'label' => __('Item view style 3')
            ],
			[
                'value' =>    4,
                'label' => __('Item view style 4')
            ],
			[
                'value' =>    5,
                'label' => __('Item view style 5')
            ],
			[
                'value' =>    6,
                'label' => __('Item view style 6')
            ],
			[
                'value' =>    7,
                'label' => __('Item view style 7')
            ],
			[
                'value' =>    8,
                'label' => __('Item view style 8')
            ],
        ];
    }
}
