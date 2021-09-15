<?php
namespace Wanexo\Brand\Model\Config\Source;

class Brandsidebar implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
			[
			  'value'=>   0,
			  'label'=> __('Select Sidebar')
			],
			[
			  'value'=>   'left',
			  'label'=> __('Left')
			],
			[
			  'value'=>   'right',
			  'label'=> __('Right')
			]
		];
    }
}
