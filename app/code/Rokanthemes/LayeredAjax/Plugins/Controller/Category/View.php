<?php
namespace Rokanthemes\LayeredAjax\Plugins\Controller\Category;
use Magento\Catalog\Controller\Category\View as Sb;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface as IResult;
use Magento\Framework\View\Result\Page;
use Rokanthemes\LayeredAjax\Helper\Data as H;
class View {
	/**
	 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
	 * 2019-12-14
	 * «Argument 2 passed to Rokanthemes\LayeredAjax\Plugins\Controller\Category\View::afterExecute()
	 * must implement interface Magento\Framework\Controller\ResultInterface, null given»:
	 * https://github.com/royalwholesalecandy/core/issues/31
	 * @param Sb $sb
	 * @param IResult|Page|null $r [optional]
	 * @return IResult|null
	 */
	function afterExecute(Sb $sb, IResult $r = null) {
		/**
		 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
		 * «Call to a member function getLayout() on null
		 * in app/code/Rokanthemes/LayeredAjax/Plugins/Controller/Category/View.php:20»:
		 * https://github.com/royalwholesalecandy/core/issues/25
		 */
		$h = df_o(H::class); /** @var H $h */
		if ($r instanceof Page && df_request('isAjax') && $h->isEnabled()) {
			$navigation = df_layout()->getBlock('catalog.leftnav');
			$products = df_layout()->getBlock('category.products');
			$res = $sb->getResponse(); /** @var Http $res */
			$res->representJson(df_json_encode([
				'navigation' => $navigation->toHtml(), 'products' => $products->toHtml()
			]));
			/**
			 * 2019-12-13 Dmitry Fedyuk https://github.com/mage2pro
			 * @see \Magento\Framework\App\Action\Action::dispatch():
			 * 		return $result ?: $this->_response;
			 */
			$r = null;
		}
		return $r;
	}
}
