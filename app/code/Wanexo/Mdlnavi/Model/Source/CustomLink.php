<?php
namespace Wanexo\Mdlnavi\Model\Source;

class CustomLink extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('None'), 'value' => ''],
                ['label' => __('Pages'), 'value' => 'pages'],
                ['label' => __('Custom Links'), 'value' => 'links'],
            ];
        }
        return $this->_options;
    }
}