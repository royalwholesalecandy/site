<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Themeoption\Model\System\Config\Source;

class Bgattachment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'scroll', 'label' => __('Scroll')],
			['value' => 'fixed', 'label' => __('Fixed')]
			];
    }
}
