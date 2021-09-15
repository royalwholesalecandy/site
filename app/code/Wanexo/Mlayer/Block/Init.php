<?php
namespace Wanexo\Mlayer\Block;
class Init extends \Magento\Backend\Block\AbstractBlock {
	protected function _construct() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$page = $objectManager->get('Magento\Framework\View\Page\Config');
        $page->addPageAsset('Wanexo_Mlayer::js/camera.js');
	}
}