<?php

namespace BoostMyShop\AvailabilityStatus\Block\Adminhtml\System\Config;

class LeadTimeRanges extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\AvailabilityStatus\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_config = $config;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/leadTimeRanges.phtml');
        }
        return $this;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        return $this->_toHtml();
    }

    public function getFieldValue($type, $index)
    {

        if ($this->_storeManager->isSingleStoreMode()) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
            $scopeId = 1;
        }
        else {
            $scope = 'default';
            $scopeId = 0;
            if ($this->getRequest()->getParam('website'))
            {
                $scope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
                $scopeId = $this->getRequest()->getParam('website');
            }
            if ($this->getRequest()->getParam('store'))
            {
                $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $scopeId = $this->getRequest()->getParam('store');
            }
        }

        return $this->_config->getSetting('backorder/'.$type.'_'.$index, $scopeId, $scope);
    }

}
