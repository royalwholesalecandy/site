<?php

namespace Magewares\MWQuickOrder\Plugin;
class HelperProductView
{
    protected $_coreRegistry;

    public function __construct(
		\Magento\Framework\Registry $coreRegistry
        ) {
		$this->_coreRegistry = $coreRegistry;

    }

    public function afterInitProductLayout(
        \Magento\Catalog\Helper\Product\View $subject
        )
    {
		$product = $this->_coreRegistry->registry('current_product');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resultPage = $objectManager->create('\Magento\Framework\View\Result\Page');
		$resultPage->addHandle('catalog_product_view_type_'.$product->getTypeId());
		return $resultPage;

    }
}
