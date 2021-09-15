<?php
namespace Wanexo\Brand\Model\Brand\Source;

use Magento\Framework\Option\ArrayInterface;
use Wanexo\Brand\Model\Brand;

class IsActive implements ArrayInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => Brand::STATUS_ENABLED,
                'label' => __('Enabled')
            ],[
                'value' => Brand::STATUS_DISABLED,
                'label' => __('Disabled')
            ],
        ];
    }

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
