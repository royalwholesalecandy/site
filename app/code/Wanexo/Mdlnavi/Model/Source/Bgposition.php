<?php
/**
 * Used in creating options for Banner Types
 *
 */
namespace Wanexo\Mdlnavi\Model\Source;

class Bgposition extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('Top'), 'value' => 'top'],
                ['label' => __('Right'), 'value' => 'right'],
				['label' => __('Bottom'), 'value' => 'bottom'],
				['label' => __('Left'), 'value' => 'left'],
				['label' => __('Center'), 'value' => 'center'],
            ];
        }
        return $this->_options;
    }
}