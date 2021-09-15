<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mlayer\Model\Source;

class Bannertype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 1, 'label' => __('Camera Slider')],
			['value' => 2, 'label' => __('Owl Slider')],
			['value' => 3, 'label' => __('Grid Banner')]
		];
    }
}
