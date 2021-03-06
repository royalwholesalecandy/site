<?php

namespace TemplateMonster\PromoBanner\Block\Adminhtml\Widget;

class Editor extends \Magento\Backend\Block\Widget\Form\Element
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var Factory
     */
    protected $_factoryElement;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        $data = []
    ) {
        $this->_factoryElement = $factoryElement;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $editor = $this->_factoryElement->create('editor', ['data' => $element->getData()])
            ->setLabel('')
            ->setForm($element->getForm())
            ->setWysiwyg(true)
            ->setConfig($this->_wysiwygConfig->getConfig([
                'enabled' => false,
                'add_variables' => false,
                'add_widgets' => true,
                'add_images' => true]));

        if ($element->getRequired()) {
            $editor->addClass('required-entry');
        }

        $element->setData(
            'after_element_html',
            $this->_getAfterElementHtml() . $editor->getElementHtml()
        );

        return $element;
    }

    /**
     * @return string
     */
    protected function _getAfterElementHtml()
    {
        $html = <<<HTML
    <style>
        .admin__field-control.control .control-value,
        .scalable.action-add-widget.plugin {
            display: none !important;
        }
        .admin__scope-old textarea {
            min-height: 35px;
            height: 35px;
        }
    </style>
HTML;

        return $html;
    }
}