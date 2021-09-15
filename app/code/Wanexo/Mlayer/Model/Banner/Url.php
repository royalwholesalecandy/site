<?php
namespace Wanexo\Mlayer\Model\Banner;

use Magento\Framework\UrlInterface;
use Wanexo\Mlayer\Model\Banner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Url
{
    const LIST_URL_CONFIG_PATH = 'wanexo_mlayer/banner/list_url';
    const URL_PREFIX_CONFIG_PATH = 'wanexo_mlayer/banner/url_prefix';
    const URL_SUFFIX_CONFIG_PATH = 'wanexo_mlayer/banner/url_suffix';
    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    protected $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function getListUrl()
    {
        $sefUrl = $this->scopeConfig->getValue(self::LIST_URL_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if ($sefUrl) {
            return $this->urlBuilder->getUrl('', ['_direct' => $sefUrl]);
        }
        return $this->urlBuilder->getUrl('wanexo_mlayer/banner/index');
    }

    /**
     * @param Banner $banner
     * @return string
     */
    public function getBannerUrl(Banner $banner)
    {
        if ($urlKey = $banner->getUrlKey()) {
            $prefix = $this->scopeConfig->getValue(
                self::URL_PREFIX_CONFIG_PATH,
                ScopeInterface::SCOPE_STORE
            );
            $suffix = $this->scopeConfig->getValue(
                self::URL_SUFFIX_CONFIG_PATH,
                ScopeInterface::SCOPE_STORE
            );
            $path = (($prefix) ? $prefix . '/' : '').
                $urlKey .
                (($suffix) ? '.'. $suffix : '');
            return $this->urlBuilder->getUrl('', ['_direct'=>$path]);
        }
        return $this->urlBuilder->getUrl('wanexo_mlayer/banner/view', ['id' => $banner->getId()]);
    }
}
