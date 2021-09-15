<?php
/**
 * 
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Mblockwidth extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('100%'), 'value' => '100'],
                ['label' => __('75%'), 'value' => '75'],
				['label' => __('50%'), 'value' => '50'],
				['label' => __('25%'), 'value' => '25'],
            ];
        }
        return $this->_options;
    }
}
