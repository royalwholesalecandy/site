<?php
namespace Wanexo\Mlayer\Model\Banner\Source;

use Magento\Framework\Option\ArrayInterface;

class Type implements ArrayInterface
{
    const LEFT = 1;
    const RIGHT = 2;
	const CENTER = 3;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_options = [
            [
                'value' => self::LEFT,
                'label' => __('Left')
            ],
            [
                'value' => self::RIGHT,
                'label' => __('Right')
            ],
			[
                'value' => self::CENTER,
                'label' => __('Center')
            ],
        ];
        return $_options;
    }

    //TODO move this in parent class
    /**
     * get options as key value pair
     *
     * @param array $options
     * @return array
     */
    public function getOptions(array $options = [])
    {
        $_tmpOptions = $this->toOptionArray($options);
        $_options = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}
