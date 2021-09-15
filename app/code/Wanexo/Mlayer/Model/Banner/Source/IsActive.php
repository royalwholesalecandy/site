<?php
namespace Wanexo\Mlayer\Model\Banner\Source;

use Magento\Framework\Option\ArrayInterface;
use Wanexo\Mlayer\Model\Banner;

class IsActive implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Banner::STATUS_ENABLED,
                'label' => __('Yes')
            ],[
                'value' => Banner::STATUS_DISABLED,
                'label' => __('No')
            ],
        ];
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function getOptions()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}
