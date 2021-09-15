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

class ScopedModel extends \Magento\Framework\Model\AbstractModel{

    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function  __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_objectManager = $objectManagerInterface;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
/*
    public function load($id, $field = null, \Itoris\PendingRegistration\Model\Scope $scope = null){
        $this->_getResource()->load($this, $id, $field, $scope);
        return $this;
    }
*/
        /**
     *
     * @param \Itoris\PendingRegistration\Model\Scope $scope
     * @return void
     */
    public function setScope(\Itoris\PendingRegistration\Model\Scope $scope){
        $this->setData('scope', $scope->getTightType());
        $this->setData('scope_area', $scope->getTightArea());
    }

    /**
     * @return \Itoris\PendingRegistration\Model\Scope
     */
    public function getScope(){
        /** @var $scope \Itoris\PendingRegistration\Model\Scope */
        $scope = $this->_objectManager->create('Itoris\PendingRegistration\Model\Scope');
        $scope->setDefault(null);

        $type = $this->getData('scope');
        $typeArea = $this->getData('scope_area');
        if($type == \Itoris\PendingRegistration\Model\Scope::$CONFIGURATION_SCOPE_STORE){
            $scope->setStoreId($typeArea);
        }
        if($type == \Itoris\PendingRegistration\Model\Scope::$CONFIGURATION_SCOPE_WEBSITE){
            $scope->setWebsiteId($typeArea);
        }
        if($type == \Itoris\PendingRegistration\Model\Scope::$CONFIGURATION_SCOPE_DEFAULT){
            $scope->setDefault($typeArea);
        }
        return $scope;
    }

    /**
     * @return \Itoris\PendingRegistration\Model\ResourceModel\ScopedModel
     */
    protected function _getResource(){
        return parent::_getResource();
    }

    public function recordExists($id, $field = null, \Itoris\PendingRegistration\Model\Scope $scope = null){
        return $this->_getResource()->recordExists($id, $field, $scope);
    }

}
