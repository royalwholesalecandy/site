<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\App\Area\FrontNameResolver;

class Customer extends AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var State
     */
    protected $appState;

    /**
     * Customer constructor.
     *
     * @param Context $context
     * @param GroupManagementInterface $groupManagement
     * @param State $appState
     */
    public function __construct(
        Context $context,
        GroupManagementInterface $groupManagement,
        State $appState
    ) {
        $this->groupManagement = $groupManagement;
        $this->appState        = $appState;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->context       = $this->objectManager->get('Magento\Framework\App\Http\Context');

        parent::__construct($context);
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentCustomerId()
    {
        $customerId = null;
        if ($this->appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
            $customer = $this->objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomer();

            return $customer->getId();
        }

        $customerSession = $this->objectManager->create('Magento\Customer\Model\SessionFactory')->create();
        $customerId      = $customerSession->getCustomer()->getId();

        return $customerId;
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->objectManager->get('Magento\Customer\Model\Session')->getCustomer()->getWebsiteId();
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCustomerGroupId()
    {
        $groupId = $this->context->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);

        if ($groupId === null) {
            $groupId = $this->context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        }

        if ($groupId === null) {
            $customerSession = $this->objectManager->get('Magento\Customer\Model\Session');
            if ($customerSession->isLoggedIn()) {
                $groupId = $customerSession->getCustomer()->getGroupId();
            }

            if ($groupId === null) {
                $groupId = $this->groupManagement->getNotLoggedInGroup()->getId();
            }
        }

        return $groupId;
    }
}