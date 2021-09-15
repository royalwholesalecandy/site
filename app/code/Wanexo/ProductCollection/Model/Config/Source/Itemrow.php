<?php
namespace Wanexo\ProductCollection\Model\Config\Source;

class Itemrow implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return [
			['value' =>    'col-lg-6 col-md-3 col-sm-4 col-xs-6  clear-item-2',
			 'label' => __('2')
			],
			['value' =>    'col-lg-4 col-md-3 col-sm-4 col-xs-6  clear-item-3',
			 'label' => __('3')
			],
			['value' =>    'col-lg-3 col-md-3 col-sm-4 col-xs-6  clear-item-4',
			 'label' => __('4')
			],
			['value' =>    'grid-5 col-md-3 col-sm-4 col-xs-6  clear-item-5',
			 'label' => __('5')
			],
			['value' =>    'col-lg-2 col-md-3 col-sm-4 col-xs-6  clear-item-6',
			 'label' => __('6')
			]
        ];
    }
}