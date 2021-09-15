<?php


namespace Metagento\Referrerurl\Block;


class Cookie extends
    \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Helper\Context $contextHelper,
        array $data
    ) {
        \Magento\Framework\View\Element\Template::__construct($context, $data);
        $this->_contextHelper = $contextHelper;
        $this->saveReferrerUrl();
    }

    public function _construct()
    {
        parent::_construct();
//        $this->setTemplate('Metagento_Referrerurl::cookie.phtml');
    }

    public function saveReferrerUrl()
    {
        $referrerUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ( $this->validReferrerUrl($referrerUrl) ) {
            setcookie('referrer_url', $referrerUrl, time() + 60 * 60 * 24 * 30);
        }
        return $this;
    }

    protected function getDomain( $url )
    {
        $url = str_replace('http://', '', $url);
        $url = str_replace('https://', '', $url);
        $url = str_replace('www.', '', $url);
        return $url;
    }

    protected function validReferrerUrl( $referrerUrl )
    {
        $referrerUrl = $this->getDomain($referrerUrl);
        $storeUrl    = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $storeUrl    = $this->getDomain($storeUrl);
        if ( !$referrerUrl ) {
            return false;
        }
        if(strpos($referrerUrl, $storeUrl) === 0 ){
            return false;
        }
        foreach ( $this->getIgnoredDomains() as $domain ) {
            if ( strpos($referrerUrl, $domain) === 0 ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param null $store
     * @return array
     */
    public function getIgnoredDomains($store = null){
        $domains = $this->_scopeConfig->getValue('referrerurl/general/ignored_domains', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $domains = explode("\n",$domains);
        return array_filter($domains);
    }

}