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
 class Settings extends ScopedModel
 {
    protected function _construct(){
        $this->_init( 'Itoris\PendingRegistration\Model\ResourceModel\Settings' );
    }

    /**
     * @static
     * @return \Itoris\PendingRegistration\Model\Settings
     */
    public static function inst(){
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\PendingRegistration\Model\Settings');
    }

     /**
      * @param null $scope
      * @return bool
      */
    public function isEngineActive($scope = null){
        $this->load( 'active', 'name', $scope );
        return (bool)$this->getValue();
    }

    public function setName($name){
        $this->setData('name', $name);
    }

    public function getName(){
        return $this->getData('name');
    }

    public function setValue($value){
        $this->setData('value', $value);
    }

    public function getValue(){
        return $this->getData('value');
    }
 }
