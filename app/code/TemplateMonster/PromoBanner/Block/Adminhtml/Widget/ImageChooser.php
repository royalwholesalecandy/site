<?php

namespace TemplateMonster\PromoBanner\Block\Adminhtml\Widget;

use Magento\Framework\Data\Form\Element\AbstractElement as Element;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Data\Form\Element\Factory as FormElementFactory;
use Magento\Backend\Block\Template;


class ImageChooser extends Template
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    protected $_backendUrl;

    /**
     * @param TemplateContext $context
     * @param FormElementFactory $elementFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        FormElementFactory $elementFactory,
        $data = []
    ) {
        $this->_backendUrl = $backendUrl;
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Element $element
     * @return Element
     */
    public function prepareElementHtml(Element $element)
    {
        $config = $this->_getData('config');
        $sourceUrl = $this->_backendUrl->getUrl('cms/wysiwyg_images/index',
            ['target_element_id' => $element->getHtmlId(), 'type' => 'file']);

        /** @var \Magento\Backend\Block\Widget\Button $chooser */
        $chooser = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel($config['button']['open'])
            ->setOnClick('MediabrowserUtility.openDialog(\''. $sourceUrl .'\')')
            ->setDisabled($element->getReadonly());

        /** @var \Magento\Framework\Data\Form\Element\Text $input */
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text");
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData('after_element_html', $input->getElementHtml() . $this->_getAfterElementHtml() . $chooser->toHtml());

        return $element;
    }

    /**
     * @return string
     */
    protected function _getAfterElementHtml()
    {
        $html = <<<HTML
    <style>
        .admin__field-control.control .control-value {
            display: none !important;
        }
        .admin__field-note {
            margin-top: 0px;
            line-height: 18px;
        }
        .admin__fieldset > .admin__field > .admin__field-control {
            line-height: 3.2rem;
        }
        .admin__scope-old #widget_instace_tabs_properties_section_content .widget-option-label {
            margin-top: 0;
        }
        .admin__scope-old .fieldset .field {
            margin-bottom: 15px;
        }
        .admin__scope-old .fieldset .field.admin__field[class*=_cms_block] {
            margin-bottom: 0;
        }
        input[id$=_image_url] {
            margin-bottom: 10px;
        }
    </style>
HTML;

        return $html;
    }
}