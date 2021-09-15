<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Labelcolor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('Red'), 'value' => 'red'],
                ['label' => __('Yellow'), 'value' => 'yellow'],
				['label' => __('Green'), 'value' => 'green'],
            ];
        }
        return $this->_options;
    }
}
