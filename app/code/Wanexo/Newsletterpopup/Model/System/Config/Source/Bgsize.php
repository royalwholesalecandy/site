<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Newsletterpopup\Model\System\Config\Source;

class Bgsize implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'none', 'label' => __('None')],
			['value' => '100%', 'label' => __('100%')],
			['value' => 'cover', 'label' => __('Cover')]
			];
    }
}
