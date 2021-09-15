<?php
/**
 * AmazonMagentoConnect Admin GetAmazonCategories Block.
 * @category  Webkul
 * @package   Webkul_AmazonMagentoConnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;

class GetAmazonCategories extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/button/getamazoncategories.phtml';

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }

        return $this;
    }
    /**
     * Render button.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return ajax url for button.
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('amazonmagentoconnect/categories/import');
    }

    /**
     * Get the button and scripts contents.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'amazonconnect_import_categories_button',
                'button_label' => __('Import Amazon Categories'),
                'onclick' => 'javascript:check(); return false;',
            ]
        );
        return $this->_toHtml();
    }
}
