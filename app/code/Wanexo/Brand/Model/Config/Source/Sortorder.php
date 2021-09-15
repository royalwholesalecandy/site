<?php
namespace Wanexo\Brand\Model\Config\Source;

class Sortorder implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return
		[
			[
			  'value'=>   'brand_option_name',
			  'label'=> __('By Name')
			],
			[
			  'value'=>   'position',
			  'label'=> __('By Postion')
			],
			[
			  'value'=>   'brand_id',
			  'label'=> __('By ID')
			]
		];
    }
}
