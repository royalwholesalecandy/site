<?php
namespace Mirasvit\SearchReport\Plugin;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Mirasvit\SearchReport\Api\Service\LogServiceInterface;
use Mirasvit\SearchReport\Model\Log;
class ResponsePlugin
{
	const COOKIE_NAME = 'searchReport-log';

	/**
	 * @var \Magento\Framework\App\Request\Http
	 */
	private $request;

	/**
	 * @var LogServiceInterface
	 */
	private $logService;

	/**
	 * @var CookieManagerInterface
	 */
	private $cookieManager;

	/**
	 * @var CookieMetadataFactory
	 */
	private $cookieMetadataFactory;

	/**
	 * @var Registry
	 */
	private $registry;

	public function __construct(
		RequestInterface $request,
		LogServiceInterface $logService,
		CookieManagerInterface $cookieManager,
		CookieMetadataFactory $cookieMetadataFactory,
		Registry $registry
	) {
		$this->request = $request;
		$this->logService = $logService;
		$this->cookieManager = $cookieManager;
		$this->cookieMetadataFactory = $cookieMetadataFactory;
		$this->registry = $registry;
	}

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function beforeSendResponse(ResponseInterface $response)
	{
		/** @var \Magento\Framework\App\Response\Http $response */

		if ($this->request->getParam('q')) {
			$query = $this->request->getParam('q');
			$misspell = $this->request->getParam('o');
			$fallback = $this->request->getParam('f');
			$results = $this->registry->registry(SearchPlugin::REGISTRY_KEY);
			$source = $this->request->getFullActionName();

			if ($results === null) {
				return;
			}

			$log = $this->logService->logQuery($query, $results, $source, $misspell, $fallback);
			/**
			 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
			 * 1) «Call to undefined method Mirasvit\SearchReport\Service\LogService::getId()
			 * in app/code/Mirasvit/SearchReport/Plugin/ResponsePlugin.php:92»:
			 * https://github.com/royalwholesalecandy/core/issues/39
			 * 2) @uses \Mirasvit\SearchReport\Service\LogService::logQuery() can return `$this`
			 */
			if ($log instanceof Log) {
				$this->setLogCookie($log->getId());
			}
		} else {
			$logId = $this->cookieManager->getCookie(self::COOKIE_NAME);
			$this->logService->logClick($logId);

			$this->setLogCookie(0);
		}
	}

	private function setLogCookie($logId)
	{
		$metadata = $this->cookieMetadataFactory->createPublicCookieMetadata([
			'path' => '/',
		]);

		/*
		* If enabled - allows subdomains
		*/
		// $metadata->setDomain($_SERVER['HTTP_HOST']);
		$metadata->setSecure(isset($_SERVER['HTTPS']));
		$metadata->setHttpOnly(true);

		$this->cookieManager->setPublicCookie(self::COOKIE_NAME, $logId, $metadata);
	}
}