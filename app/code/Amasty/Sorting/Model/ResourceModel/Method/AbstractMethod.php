<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
namespace Amasty\Sorting\Model\ResourceModel\Method;

use Amasty\Sorting\Api\MethodInterface;

/**
 * Class AbstractMethod
 *
 * @package Amasty\Sorting\Model\Method
 */
abstract class AbstractMethod extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements MethodInterface
{
    /**
     * @var bool
     */
    const ENABLED = true;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $methodCode;

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var \Amasty\Sorting\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * AbstractMethod constructor.
     *
     * @param Context $context
     * @param string  $connectionName
     * @param string  $methodCode
     * @param string  $methodName
     */
    public function __construct(
        Context $context,
        $connectionName = null,
        $methodCode = '',
        $methodName = ''
    ) {
        $this->scopeConfig      = $context->getScopeConfig();
        $this->request          = $context->getRequest();
        $this->storeManager     = $context->getStoreManager();
        $this->helper           = $context->getHelper();
        $this->logger           = $context->getLogger();
        $this->date             = $context->getDate();
        $this->methodCode       = $methodCode;
        $this->methodName       = $methodName;
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        // dummy
    }

    /**
     * {@inheritdoc}
     */
    abstract public function apply($collection, $direction);

    /**
     * Is sorting method enabled by config
     *
     * @return bool
     */
    public function isActive()
    {
        return !$this->helper->isMethodDisabled($this->getMethodCode());
    }

    /**
     * @return string
     */
    public function getMethodCode()
    {
        if (empty($this->methodCode)) {
            $this->logger->warning('Undefined Amasty sorting method code, add method code to di.xml');
        }
        return $this->methodCode;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        if (empty($this->methodCode)) {
            $this->logger->warning('Undefined Amasty sorting method code, add method code to di.xml');
        }
        return $this->methodName;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodLabel($store = null)
    {
        return __($this->getMethodName());
    }
}
