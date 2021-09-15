<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model;

/**
 * Class Logger
 * @package Amasty\Sorting\Model
 */
class Logger
{
    const DEBUG_CONFIG_PATH = 'amsorting/general/debug';
    const DEBUG_REQUEST_VAR = 'amdebug';
    
    /**
     * @var \Magento\Framework\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Logger constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     */
    public function logCollectionQuery($collection)
    {
        if ($this->scopeConfig->isSetFlag(self::DEBUG_CONFIG_PATH)
            && $this->request->getParam(self::DEBUG_REQUEST_VAR, false)
        ) {
            $this->logger->log(\Monolog\Logger::INFO, $collection->getSelect()->__toString());
        }
        return $this;
    }
}
