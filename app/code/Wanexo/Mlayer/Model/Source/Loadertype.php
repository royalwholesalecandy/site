<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mlayer\Model\Source;

class Loadertype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'bar', 'label' => __('Bar')], ['value' => 'pie', 'label' => __('Pie')]];
    }
}
