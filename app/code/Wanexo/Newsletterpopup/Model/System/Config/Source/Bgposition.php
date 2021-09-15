<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Newsletterpopup\Model\System\Config\Source;

class Bgposition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'center center', 'label' => __('Center Center')],
			['value' => 'left top', 'label' => __('Left Top')],
			['value' => 'right top', 'label' => __('Right Top')],
			['value' => 'left bottom', 'label' => __('Left Bottom')],
			['value' => 'right bottom', 'label' => __('Right Bottom')]
			];
    }
}
