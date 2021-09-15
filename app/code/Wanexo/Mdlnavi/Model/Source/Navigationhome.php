<?php
/**
 * Used in creating options for navigation settings
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Navigationhome extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
				['label' => __('None'), 'value' => '0'],
                ['label' => __('Home link'), 'value' => '1'],
                ['label' => __('Home icon'), 'value' => '2'],
				['label' => __('Home link with icon'), 'value' => '3'],
				['label' => __('Remove home icon or link'), 'value' => '4'],
            ];
        }
        return $this->_options;
    }
}