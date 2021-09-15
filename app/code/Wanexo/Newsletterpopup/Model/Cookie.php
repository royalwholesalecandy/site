<?php 

namespace Wanexo\Newsletterpopup\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;


class Cookie
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'show_np';

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    
    
     private $publicCookieMetaData;
    
    private  $phpCookieManager ;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        PublicCookieMetadata $publicCookieMetaData,
        PhpCookieManager $phpCookieManager
        
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->publicCookieMetaData = $publicCookieMetaData;
        $this->phpCookieManager = $phpCookieManager;
    }

     /**
     * Get form key cookie
     *
     * @return string
     */
    public function get()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }
    /**
     * @param string $value
     * @param PublicCookieMetadata $metadata
     * @return void
     */
    public function set($value, $duration = 86400)
    {
       
       
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
          ->setDuration($duration)
          ->setPath($this->sessionManager->getCookiePath())
          ->setDomain($this->sessionManager->getCookieDomain());
       
     $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $metadata
        );
    }
    /**
     * @return void
     */
    public function delete()
    {
        $this->cookieManager->deleteCookie(
            self::COOKIE_NAME,
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }
}


      