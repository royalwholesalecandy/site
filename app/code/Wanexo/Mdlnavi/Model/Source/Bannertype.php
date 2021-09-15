<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Bannertype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('Default'), 'value' => 'default'],
                ['label' => __('Megamenu'), 'value' => 'megamenu'],
            ];
        }
        return $this->_options;
    }
}
