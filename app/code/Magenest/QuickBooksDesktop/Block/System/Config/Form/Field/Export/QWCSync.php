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
use Magenest\QuickBooksDesktop\Model\Config\Source\Queue\TypeQuery;

/**
 * Class QWC
 *
 * @package Magenest\QuickBooksDesktop\Block\System\Config\Form\Field\Export
 */
class QWCSync extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magenest\QuickBooksDesktop\Model\Connector
     */
    protected $_connector;

    /**
     * QWC constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magenest\QuickBooksDesktop\Model\Connector $connector
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magenest\QuickBooksDesktop\Model\Connector $connector,
        array $data = []
    ) {
        $this->_connector = $connector;
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
        $config = $this->_connector->getUser();
        if ($config) {
            /** @var \Magento\Backend\Block\Widget\Button $buttonBlock */
            $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

            $params = [
                'website' => $buttonBlock->getRequest()->getParam('website'),
                'type' => TypeQuery::QUERY_SYNC
            ];

            $url = $this->getUrl("qbdesktop/QWC/export", $params);
            $data = [
                'id' => 'system_qwc_sync',
                'label' => __('Synchronization from Magento'),
                'onclick' => "setLocation('" . $url . "')",
            ];

            $html = $buttonBlock->setData($data)->toHtml();

            return $html;
        } else {
            $element->setDisabled('disabled');

            return $element->getElementHtml();
        }
    }


    /**
     * Return Varnish version to this class
     *
     * @return int
     */
    public function getQWCVersion()
    {
        return 4;
    }
}
