<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Rblockwidth extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('25%'), 'value' => '1'],
                ['label' => __('50%'), 'value' => '2'],
				['label' => __('75%'), 'value' => '3'],
				['label' => __('100%'), 'value' => '4'],
            ];
        }
        return $this->_options;
    }
}
