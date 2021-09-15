<?php
namespace BoostMyShop\Supplier\Block\Invoice\Form\Renderer;

use Magento\Store\Model\StoreManagerInterface;

class Attachment extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    private $_storeManager;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storemanager,
        $data = []
    ) {
        $this->_storeManager = $storemanager;
        $this->setType('file');
    }


    /**
     * Return File Download link
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        if ($this->getValue() && !is_array($this->getValue())) {
            $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $imageUrl = $mediaDirectory.'invoice_attachment/'.$this->getValue();
            $html .= '<span>';
            $html .= '<a href="' . $imageUrl . '" target="_blank">'.$this->getValue().'</a>';
            $html .= '</span>';
        }

        return $html . $this->_getHiddenInput() . $this->_getDeleteCheckboxHtml();
    }

    /**
     * Return Delete File CheckBox HTML
     *
     * @return string
     */
    protected function _getDeleteCheckboxHtml()
    {
        $html = '';
        if ($this->getValue() && !$this->getRequired() && !is_array($this->getValue())) {
            $checkboxId = sprintf('%s_delete', $this->getHtmlId());
            $checkbox = [   
                'type' => 'checkbox',
                'name' => 'bsi_attachment[delete]',
                'value' => '1',
                'class' => 'checkbox',
                'id' => $checkboxId
            ];
            $label = ['for' => $checkboxId];
            if ($this->getDisabled()) {
                $checkbox['disabled'] = 'disabled';
                $label['class'] = 'disabled';
            }

            $html .= '<br/><span class="' . $this->_getDeleteCheckboxSpanClass() . '">';
            $html .= $this->_drawElementHtml('input', $checkbox) . ' ';
            $html .= $this->_drawElementHtml('label', $label, false) . $this->_getDeleteCheckboxLabel() . '</label>';
            $html .= '</span>';
        }
        return $html;
    }

    protected function _getPreviewHtml()
    {
        $html = '';
        $html = '';
        if ($this->getValue() && !is_array($this->getValue())) {
            $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $imageUrl = $mediaDirectory.'invoice_attachment/'.$this->getValue();
            $html .= '<span>';
            $html .= '<a href="' . $imageUrl . '" target="_blank">'.$this->getValue().'</a>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Return Hidden element with current value
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        return $this->_drawElementHtml(
            'input',
            [
                'type' => 'hidden',
                'name' => sprintf('bsi_attachment[value]'),
                'id' => sprintf('%s_value', $this->getHtmlId()),
                'value' => $this->getEscapedValue()
            ]
        );
    }

    /**
     * Return Delete CheckBox SPAN Class name
     *
     * @return string
     */
    protected function _getDeleteCheckboxSpanClass()
    {
        return 'delete-file';
    }

    protected function _getDeleteCheckboxLabel()
    {
        return __('Delete File');
    }

    protected function _drawElementHtml($element, array $attributes, $closed = true)
    {
        $parts = [];
        foreach ($attributes as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return sprintf('<%s %s%s>', $element, implode(' ', $parts), $closed ? ' /' : '');
    }

    public function getEscapedValue($index = null)
    {
        if (is_array($this->getValue())) {
            return false;
        }
        $value = $this->getValue();
        if (is_array($value) && $index === null) {
            $index = 'value';
        }

        return parent::getEscapedValue($index);
    }
    
}