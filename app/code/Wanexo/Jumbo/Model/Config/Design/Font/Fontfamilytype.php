<?php
namespace Wanexo\Jumbo\Model\Config\Design\Font;

class Fontfamilytype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'google', 'label' => __('Google Fonts')], 
            ['value' => 'dfont', 'label' => __('Default Fonts')], 
            
        ];
    }
}
