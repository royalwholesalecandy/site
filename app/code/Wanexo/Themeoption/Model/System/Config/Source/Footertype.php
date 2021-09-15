<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Themeoption\Model\System\Config\Source;

class Footertype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
				['value' => '1', 'label' => __('Footer Type 01')],
				['value' => '2', 'label' => __('Footer Type 02')],
				['value' => '3', 'label' => __('Footer Type 03')],
				['value' => '4', 'label' => __('Footer Type 04')],
				['value' => '5', 'label' => __('Footer Type 05')]
			];
    }
}
