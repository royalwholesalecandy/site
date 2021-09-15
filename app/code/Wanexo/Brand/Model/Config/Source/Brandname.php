<?php
namespace Wanexo\Brand\Model\Config\Source;

class Brandname implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
			[
			  'value'=>   'label_only',
			  'label'=> __('Label Only')
			],
			[
			  'value'=>   'image_only',
			  'label'=> __('Image Only')
			],
			[
			  'value'=>   'image_and_lable',
			  'label'=> __('Image and Label')
			]
		];
    }
}
