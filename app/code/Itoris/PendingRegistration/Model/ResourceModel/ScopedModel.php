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

namespace Itoris\PendingRegistration\Model\ResourceModel;

abstract class ScopedModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{

    public function load(
        \Magento\Framework\Model\AbstractModel $object,
        $value, $field=null, \Itoris\PendingRegistration\Model\Scope $scope = null)
    {

        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->getConnection();
        if ($read && !is_null($value)) {
            $select = $this->_getLoadSelect($field, $value, $object);

            if($scope !== null){
                $select->where(\Itoris\PendingRegistration\Model\Scope::getWhereSql($scope));
            }

            $select    ->order(new \Zend_Db_Expr("field(scope, 'store', 'website', 'default') asc"))
                    ->limit(1);

            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        if(method_exists($this, 'unserializeFields')){
            $this->unserializeFields($object);
        }
        $this->_afterLoad($object);

        return $this;
    }

    public function recordExists($value, $field = null, \Itoris\PendingRegistration\Model\Scope $scope = null){
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->getConnection();
        if ($read && !is_null($value)) {
            $select = $this->_getLoadSelect($field, $value, null);

            if($scope !== null){
                $select->where(\Itoris\PendingRegistration\Model\Scope::getWhereSql($scope));
            }

            $select    ->order(new \Zend_Db_Expr("field(scope, 'store', 'website', 'default') asc"))
                    ->limit(1);

            $data = $read->fetchRow($select);

            if ($data) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Itoris\PendingRegistration\Helper\Data
     */
    public function getDataHelper(){
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
        return $helper;
    }
    
}
