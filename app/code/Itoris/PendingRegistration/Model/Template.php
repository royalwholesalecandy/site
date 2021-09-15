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

class Template extends ScopedModel
{
    protected function _construct(){
        $this->_init( 'Itoris\PendingRegistration\Model\ResourceModel\Template' );
    }

    public function getType(){
        return (int)parent::getType();
    }

    public function setType($type){
        return parent::setType((int) $type);
    }

    public function isActive(){
        return (bool) $this->getData('active');
    }

    public function getEmailContent(){
        return $this->getData('email_content');
    }

    public function getFromName(){
        return $this->getData('from_name');
    }

    public function getFromEmail(){
        return $this->getData('from_email');
    }
/*
    public function getSubject(){
        return $this->getData('subject');
    }
*/
    public function getAdminEmail(){
        return $this->getData('admin_email');
    }

    public function getEmailStyles(){
        return $this->getData('email_styles');
    }

    public function getBcc(){
        return $this->getData('bcc');
    }

    public function getCc(){
        return $this->getData('cc');
    }

    /**
     * @return \Itoris\PendingRegistration\Model\ResourceModel\ScopedModel
     */
    protected function _getResource(){
        return parent::_getResource();
    }

    public function getTitleExt(){
        if(isset(self::$templateTitles[$this->getType()])){
            return self::$templateTitles[$this->getType()];
        }else{
            return '';
        }
    }

    public static $templateTitles = null;

    /**
     * @static
     * @return \Itoris\PendingRegistration\Helper\Data
     */
    public static function getDataHelper(){
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
    }

    public static $EMAIL_REG_TO_ADMIN = 1;
    public static $EMAIL_REG_TO_USER = 2;
    public static $EMAIL_APPROVED = 3;
    public static $EMAIL_DECLAINED = 4;
}
\Itoris\PendingRegistration\Model\Template::$templateTitles =
    [
        1 => __('Email to admin when account created'),
        2 => __('Email to user when account created'),
        3 => __('Email to user if account approved'),
        4 => __('Email to user if account registration declined')
    ];
