<?php
namespace Wanexo\Themeoption\Model\System\Config\Source;

class Headingtype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return [
            [
                'value' =>    '1',
                'label' => __('Heading Type 01')
            ],[
                'value' =>    '2',
                'label' => __('Heading Type 02')
            ],
			[
                'value' =>    '3',
                'label' => __('Heading Type 03')
            ],[
                'value' =>    '4',
                'label' => __('Heading Type 04')
            ],
			[
                'value' =>    '5',
                'label' => __('Heading Type 05')
            ],[
                'value' =>    '6',
                'label' => __('Heading Type 06')
            ],
        ];
    }
}
