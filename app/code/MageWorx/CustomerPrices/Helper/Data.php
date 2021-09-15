<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Filesystem\Directory\ReadFactory;
use \Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * MageWorx data helper
 */
class Data extends AbstractHelper
{
    const PRICE_TYPE_FIXED   = 1;
    const PRICE_TYPE_PERCENT = 2;

    const ENABLE_CUSTOMER_PRICE_IN_CATALOG_PRICE_RULE =
        'mageworx_customerprices/main/enabled_customer_price_in_catalog_price_rule';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory
    ) {
        $this->customerSession    = $customerSession;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory        = $readFactory;
        parent::__construct($context);
    }

    /**
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isEnabledCustomerPriceInCatalogPriceRule($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_CUSTOMER_PRICE_IN_CATALOG_PRICE_RULE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}