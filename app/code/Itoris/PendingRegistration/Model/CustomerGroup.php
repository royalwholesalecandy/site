<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PendingRegistration\Model;

class CustomerGroup extends \Magento\Framework\Model\AbstractModel {

    protected $usedParentValue = false;
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        $this->_objectManager = $objectManagerInterface;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct() {
        $this->_init('Itoris\PendingRegistration\Model\ResourceModel\CustomerGroup');
    }

    public function saveGroups($groups, $websiteId, $storeId, $useParent = false) {
        $allGroups = $this->getCollection()
            ->addFieldToFilter('website_id', ['eq' => $websiteId])
            ->addFieldToFilter('store_id', ['eq' => $storeId]);
        if (!is_array($groups)) {
            $groups = [];
        }
        $existsGroup = [];
        foreach ($allGroups as $group) {
            if ($useParent || empty($groups) || !in_array($group->getGroupId(), $groups)) {
                $group->delete();
            } else {
                $existsGroup[] = $group->getGroupId();
            }
        }
        $saveAllGroupsFlag = false;
        if (!empty($groups)) {
            foreach ($groups as $groupId) {
                if ($groupId != '' && !in_array($groupId, $existsGroup)) {
                    $this->_objectManager->create('Itoris\PendingRegistration\Model\CustomerGroup')
                        ->setGroupId($groupId)
                        ->setWebsiteId($websiteId)
                        ->setStoreId($storeId)
                        ->save();
                }
            }
            $saveAllGroupsFlag = count($groups) == 1 && $groups[0] == '';
        } elseif (!$useParent) {
            $saveAllGroupsFlag = true;
        }
        if ($saveAllGroupsFlag) {
            $this->_objectManager->create('Itoris\PendingRegistration\Model\CustomerGroup')
                ->setGroupId(0)
                ->setWebsiteId($websiteId)
                ->setStoreId($storeId)
                ->setAllGroups(1)
                ->save();
        }

        return $this;
    }

    public function getGroups($websiteId, $storeId) {
        $allGroups = $this->getCollection()
            ->addFieldToFilter('website_id', ['in' => [0, $websiteId]])
            ->addFieldToFilter('store_id', ['in' => [0, $storeId]]);

        $groups = [];
        if (count($allGroups)) {
            $storeGroups = [];
            $websiteGroups = [];
            $storeGroupsAll = false;
            $websiteGroupsAll = false;
            foreach ($allGroups as $group) {
                if ($group->getStoreId()) {
                    if ($group->getAllGroups()) {
                        $storeGroupsAll = true;
                        $storeGroups[] = 'all';
                    } else {
                        $storeGroups[] = $group->getGroupId();
                    }
                } elseif ($group->getWebsiteId()) {
                    if ($group->getAllGroups()) {
                        $websiteGroupsAll = true;
                        $websiteGroups[] = 'all';
                    } else {
                        $websiteGroups[] = $group->getGroupId();
                    }
                } else {
                    $groups[] = $group->getGroupId();
                }
            }
            if ($storeId && !empty($storeGroups)) {
                if ($storeGroupsAll) {
                    $groups = [];
                } else {
                    $groups = $storeGroups;
                }
            } elseif ($websiteId && !empty($websiteGroups)) {
                 if ($websiteGroupsAll) {
                     $groups = [];
                 } else {
                     $groups = $websiteGroups;
                 }
                if ($storeId) {
                    $this->usedParentValue = true;
                }
            } elseif ($websiteId) {
                $this->usedParentValue = true;
            }
        }
        if (count($groups) == 1 && $groups[0] == 0) {
            return [];
        }
        return $groups;
    }

    public function isUsedParentValue() {
        return $this->usedParentValue;
    }
}
