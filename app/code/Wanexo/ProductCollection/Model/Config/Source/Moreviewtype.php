<?php
namespace Wanexo\ProductCollection\Model\Config\Source;

class Moreviewtype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
            [
                'value' =>    1,
				'label' => __('More view on Bottom')
            ],
			[
                'value' =>    2,
                'label' => __('More view on left')
            ]
        ];
    }
}
