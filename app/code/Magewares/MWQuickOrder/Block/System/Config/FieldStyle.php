<?php

namespace Magewares\MWQuickOrder\Block\System\Config;

class FieldStyle extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild('style_block', 'Magewares\MWQuickOrder\Block\Adminhtml\Widget\System\Config\Style');

        return parent::_prepareLayout();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getChildHtml('style_block');
    }
}