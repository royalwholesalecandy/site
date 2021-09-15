<?php

namespace TemplateMonster\PromoBanner\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class BannerType
 *
 * @package TTemplateMonster\PromoBanner\Model\Config\Source
 */
class BannerType implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'image',     'label' => __('Image')],
            ['value' => 'cms_block', 'label' => __('CMS Block')]
        ];
    }
}