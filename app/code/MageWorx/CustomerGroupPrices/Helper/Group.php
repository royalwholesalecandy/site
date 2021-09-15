<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;

class Group extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     *
     * @var CustomerGroupCollection
     */
    protected $customerGroup;

    /**
     * Group constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param GroupManagementInterface $groupManagement
     * @param CustomerGroupCollection $customerGroup
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        GroupManagementInterface $groupManagement,
        CustomerGroupCollection $customerGroup
    ) {
        $this->customerSession = $customerSession;
        $this->groupManagement = $groupManagement;
        $this->customerGroup   = $customerGroup;
        parent::__construct($context);
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCustomerGroupId()
    {
        $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $context       = $ObjectManager->get('Magento\Framework\App\Http\Context');
        $groupId       = null;

        if ($context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            $groupId = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
        }

        if ($groupId === null) {
            if ($this->customerSession->isLoggedIn()) {
                $groupId = $this->customerSession->getCustomer()->getGroupId();
            }

            if ($groupId === null) {
                $groupId = $this->groupManagement->getNotLoggedInGroup()->getId();
            }
        }

        return $groupId;
    }

    /**
     * Get array customer group
     *
     * @return array
     */
    public function getCustomersGroup()
    {
        $ids            = [];
        $customerGroups = $this->customerGroup->toOptionArray();
        foreach ($customerGroups as $group) {
            $ids[] = $group['value'];
        }

        return $ids;
    }
}