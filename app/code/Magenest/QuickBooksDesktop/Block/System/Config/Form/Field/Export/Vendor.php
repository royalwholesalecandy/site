<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Vendor
 * @package Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export
 */
class Vendor extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Vendor constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock */
        $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

        $params = [
            'website' => $buttonBlock->getRequest()->getParam('website')
        ];

        $url = $this->getUrl("qbdesktop/QWC/vendor", $params);
        $data = [
            'id' => 'system_qwc_vendor',
            'label' => __('Add Vendor'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }
}
