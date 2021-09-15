<?php
namespace Wanexo\ProductCollection\Model\Config\Source;

class Flipview implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return [
            [
                'value' =>    1,
                'label' => __('Slide')
            ],[
                'value' =>    2,
                'label' => __('Flip')
            ],
        ];
    }
}
