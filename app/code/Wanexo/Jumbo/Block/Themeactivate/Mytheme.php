<?php
namespace Wanexo\Jumbo\Block\Themeactivate;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlFactory;


class Mytheme extends Template
{
    protected $urlFactory;

    public function __construct(
        UrlFactory $urlFactory,
        Context $context,
        array $data = []
    )
    {       
        $this->urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    protected  function _construct()
    {
        parent::_construct();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
	public function getMediaUrl()
	{
		$currentStore = $this->_storeManager->getStore();
		$mediaPath = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $mediaPath;
	}
}
