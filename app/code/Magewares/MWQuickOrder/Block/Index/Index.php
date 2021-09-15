<?php

namespace Magewares\MWQuickOrder\Block\Index;

class Index extends \Magento\Framework\View\Element\Template {
	
    public function __construct(
	\Magento\Catalog\Block\Product\Context $context,
	array $data = []
	) {
        parent::__construct($context, $data);

    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
	
	public function getRowsCount(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
		return $scopeConfig->getValue('mwquickorder/general/default_rows', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getProductNameCharLength(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
		return $scopeConfig->getValue('mwquickorder/general/min_char', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function enable(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
		return $scopeConfig->getValue('mwquickorder/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	public function getStyleColor()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $style = $scopeConfig->getValue('mwquickorder/style_management/style');
        $colorStyle = $scopeConfig->getValue('mwquickorder/style_management/custom_style');
        if ($style == 'custom') {
            return '#' . $colorStyle;
        } else {
            return $style;
        }
    }
	
}