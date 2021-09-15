<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Nocol extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('1'), 'value' => '1'],
                ['label' => __('2'), 'value' => '2'],
				['label' => __('3'), 'value' => '3'],
				['label' => __('4'), 'value' => '4'],
				['label' => __('5'), 'value' => '5'],
            ];
        }
        return $this->_options;
    }
}
