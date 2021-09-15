<?php
namespace Wanexo\QuickView\Controller\Index;
/**
 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
 * «Call to undefined method Wanexo\QuickView\Controller\Index\View\Interceptor::noProductRedirect()
 * in app/code/Wanexo/QuickView/Controller/Index/View.php:66»:
 * https://github.com/royalwholesalecandy/core/issues/42
 */
class View extends \Magento\Catalog\Controller\Product\View {
	function execute()
	{
		$categoryId = (int) $this->getRequest()->getParam('category', false);
		$productId = (int) $this->getRequest()->getParam('id');
		$specifyOptions = $this->getRequest()->getParam('options');

		if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
			$product = $this->_initProduct();
			if (!$product) {
				return $this->noProductRedirect();
			}
			if ($specifyOptions) {
				$notice = $product->getTypeInstance()->getSpecifyOptionMessage();
				$this->messageManager->addNotice($notice);
			}
			if ($this->getRequest()->isAjax()) {
				$this->getResponse()->representJson(
					$this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode([
						'backUrl' => $this->_redirect->getRedirectUrl()
					])
				);
				return;
			}
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setRefererOrBaseUrl();
			return $resultRedirect;
		}

		$params = new \Magento\Framework\DataObject();
		$params->setCategoryId($categoryId);
		$params->setSpecifyOptions($specifyOptions);

		try {
			$page = $this->resultPageFactory->create(false, ['isIsolated' => true, 'template'=>'Wanexo_QuickView::layout-content.phtml']);
			$this->viewHelper->prepareAndRender($page, $productId, $this, $params);
			return $page;
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
			return $this->noProductRedirect();
		} catch (\Exception $e) {
			$this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
			$resultForward = $this->resultForwardFactory->create();
			$resultForward->forward('noroute');
			return $resultForward;
		}
	}
}