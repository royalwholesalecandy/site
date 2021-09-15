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

class Scope extends \Magento\Framework\Model\AbstractModel{


    private $scope;
    protected $_objectManager;

    public static $CONFIGURATION_SCOPE_DEFAULT = 'default';
    public static $CONFIGURATION_SCOPE_WEBSITE = 'website';
    public static $CONFIGURATION_SCOPE_STORE = 'store';

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
        $this->scope = [
            self::$CONFIGURATION_SCOPE_STORE => null,
            self::$CONFIGURATION_SCOPE_WEBSITE => null,
            self::$CONFIGURATION_SCOPE_DEFAULT => 0
        ];
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function setStoreCode($code){
        if($code !== null && !is_numeric($code)){
            $store = $this->getStoreManager()->load($code);
            $this->setStoreId((int) $store->getId());
        }else{
            $this->setStoreId($code);
        }
    }

    public function setStoreId($id){
        $this->scope[self::$CONFIGURATION_SCOPE_STORE] = $id;
    }

    public function setWebsiteCode($code){
        if($code !== null && !is_numeric($code)){
            $website = $this->getStoreManager()->getWebsite()->load($code);
            $this->setWebsiteId((int) $website->getId());
        }else{
            $this->setWebsiteId($code);
        }
    }

    public function setWebsiteId($id){
        $this->scope[self::$CONFIGURATION_SCOPE_WEBSITE] = $id;
    }

    public function getStoreId(){
        return $this->scope[self::$CONFIGURATION_SCOPE_STORE];
    }

    public function getWebsiteId(){
        return $this->scope[self::$CONFIGURATION_SCOPE_WEBSITE];
    }

    public function getDefault(){
        return $this->scope[self::$CONFIGURATION_SCOPE_DEFAULT];
    }

    public function setDefault($id){
        $this->scope[self::$CONFIGURATION_SCOPE_DEFAULT] = $id;
    }

    protected function getScope(){
        return $this->scope;
    }

    public function getSummary(){
        return $this->scope;
    }

    public function getSummaryForUrl(){
        $scope = $this->scope;
        unset($scope[self::$CONFIGURATION_SCOPE_DEFAULT]);

        if($scope[self::$CONFIGURATION_SCOPE_STORE] !== null){
            $store = $this->getStoreManager()->load($scope[self::$CONFIGURATION_SCOPE_STORE]);
            $scope[self::$CONFIGURATION_SCOPE_STORE] = $store->getCode();
        }

        if($scope[self::$CONFIGURATION_SCOPE_WEBSITE] !== null){
            $website = $this->getStoreManager()->getWebsite()->load($scope[self::$CONFIGURATION_SCOPE_WEBSITE]);
            $scope[self::$CONFIGURATION_SCOPE_WEBSITE] = $website->getCode();
        }

        return $scope;
    }

    public function getTightScope(){
        $tight = $this->_objectManager->create('Itoris\PendingRegistration\Model\Scope');
        //$tight = $this;
        $tight->setDefault(null);

        if($this->getStoreId() !== null){
            $tight->setStoreId($this->getStoreId());
            return $tight;
        }

        if($this->getWebsiteId() !== null){
            $tight->setWebsiteId($this->getWebsiteId());
            return $tight;
        }

        $tight->setDefault(0);
        return $tight;
    }

    public function getTightType(){
        $pair = $this->getTightPair();
        $pair = array_keys($pair);
        return $pair[0];
    }

    public function getTightArea(){
        $pair = $this->getTightPair();
        $pair = array_values($pair);
        return $pair[0];
    }

    protected function getTightPair(){
        foreach($this->scope as $type => $area){
            if($area !== null){
                return [$type => $area];
            }
        }
        throw new \Exception("Invalid scope object");
    }

    public static function getWhereSql(\Itoris\PendingRegistration\Model\Scope $scope){
        $scopeData = $scope->getScope();

        /** @var $resource \Magento\Framework\App\ResourceConnection */
        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        /** @var $read \Magento\Framework\DB\Adapter\AdapterInterface */
        $read = $resource->getConnection('read');

        $scopeWhere = [];
        foreach($scopeData as $scopeType => $scopeArea){
            if($scopeArea !== null){
                $scopeWhere[] = "(scope={$read->quote($scopeType)} and scope_area={$scopeArea})";
            }
        }
        $scopeWhere = implode($scopeWhere, ' or ');
        return $scopeWhere;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManager(){
        return $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
    }

}
