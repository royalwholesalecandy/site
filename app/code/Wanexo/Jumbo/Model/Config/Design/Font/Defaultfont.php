<?php
namespace Wanexo\Jumbo\Model\Config\Design\Font;

class Defaultfont implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'Arial, Helvetica, sans-serif', 'label'  => __('Arial, "Helvetica Neue", Helvetica, sans-serif')], 
			['value' => 'Georgia, serif', 'label' => __('Georgia, serif')], 
			['value' => 'Tahoma, Geneva, sans-serif', 'label' => __('Tahoma, Geneva, sans-serif')], 
            ['value' => 'Verdana, Geneva, sans-serif', 'label' => __('Verdana, Geneva, sans-serif')], 
			['value' => '"Trebuchet MS", Helvetica, sans-serif', 'label' => __('"Trebuchet MS", Helvetica, sans-serif')], 
            ['value' => '"Palatino Linotype", "Book Antiqua", Palatino, serif', 'label' => __('"Palatino Linotype", "Book Antiqua", Palatino, serif')], 
			['value' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif', 'label' => __('"Lucida Sans Unicode", "Lucida Grande", sans-serif')], 
            
        ];
    }
}
