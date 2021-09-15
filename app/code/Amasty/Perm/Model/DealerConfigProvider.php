<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amasty\Perm\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Perm\Helper\Data as Helper;
use Magento\Email\Model\Template\Filter as TemplateFilter;

class DealerConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Dealer
     */
    private $dealer;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DealerFactory
     */
    private $dealerFactory;

    /**
     * @var DealerCustomerFactory
     */
    private $dealerCustomerFactory;

    /**
     * @var Helper
     */
    private $permHelper;

    /**
     * @var TemplateFilter
     */
    private $templateFilter;

    /**
     * DealerConfigProvider constructor.
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param DealerCustomerFactory $dealerCustomerFactory
     * @param DealerFactory $dealerFactory
     * @param Helper $permHelper
     * @param TemplateFilter $templateFilter
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        DealerCustomerFactory $dealerCustomerFactory,
        DealerFactory $dealerFactory,
        Helper $permHelper,
        TemplateFilter $templateFilter
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->dealerCustomerFactory = $dealerCustomerFactory;
        $this->dealerFactory = $dealerFactory;
        $this->permHelper = $permHelper;
        $this->templateFilter = $templateFilter;
    }

    /**
     * @return Dealer
     */
    public function getDealer()
    {
        if ($this->dealer === null) {
            $dealerCustomer = $this->dealerCustomerFactory->create()
                ->load($this->checkoutSession->getQuote()->getCustomerId(), 'customer_id');

            $this->dealer = $this->dealerFactory->create()->load($dealerCustomer->getDealerId());
        }

        return $this->dealer;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        if (
            $this->permHelper->isDescriptionCheckoutMode() &&
            $this->getDealer()->getDescription()
        ) {
            $config['amasty'] = [
                'perm' => [
                    'dealerDescription' => $this->templateFilter->filter($this->getDealer()->getDescription())
                ]
            ];
        }

        return $config;
    }
}
