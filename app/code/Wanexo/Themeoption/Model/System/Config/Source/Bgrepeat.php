<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Themeoption\Model\System\Config\Source;

class Bgrepeat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'repeat', 'label' => __('Repeat')],
			['value' => 'repeat-x', 'label' => __('Repeat X')],
			['value' => 'repeat-y', 'label' => __('Repeat Y')],
			['value' => 'no-repeat', 'label' => __('No Repeat')]
			];
    }
}
