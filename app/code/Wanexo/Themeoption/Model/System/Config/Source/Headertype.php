<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Themeoption\Model\System\Config\Source;

class Headertype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
				['value' => '1', 'label' => __('Header Type 01')],
				['value' => '2', 'label' => __('Header Type 02')],
				['value' => '3', 'label' => __('Header Type 03')],
				['value' => '4', 'label' => __('Header Type 04')],
				['value' => '5', 'label' => __('Header Type 05')],
				['value' => '6', 'label' => __('Header Type 06')],
				['value' => '7', 'label' => __('Header Type 07')],
				['value' => '8', 'label' => __('Header Type 08')],
				['value' => '9', 'label' => __('Header Type 09')]
			];
    }
}
