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

//app/code/Itoris/PendingRegistration/Block/Adminhtml/Register.php
namespace Itoris\PendingRegistration\Block\Adminhtml;

class Dropdown extends \Magento\Backend\Block\Template
{
    protected function _construct(){
        $this->setTemplate('dropdown.phtml');
        parent::_construct();
    }

    public function getEngineToggleUrl(){
        return 'changeScope Url';
    }

    public function getActivateUrl(){
        return $this->getToggleUrl('activate');
    }

    public function getDeactivateUrl(){
        return $this->getToggleUrl('deactivate');
    }

    private function getToggleUrl($action){
        /** @var $hurl \Magento\Framework\Url\Helper\Data */
        $hurl = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Url\Helper\Data');
        /** @var $url \Magento\Framework\UrlInterface */
        $url = $this->_urlBuilder;
        return $url->getUrl('*/*/engine', array_merge( $this->getScope()->getSummaryForUrl(),
            [
                'action' => $action,
                'back_url' => $hurl->getCurrentBase64Url()
            ]));
    }

    public function isActive(){
        return \Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($this->getScope());
    }

    public function setScope(\Itoris\PendingRegistration\Model\Scope $scope){
        return $this->setData('scope', $scope);
    }

    /**
     * @return \Itoris\PendingRegistration\Model\Scope
     */
    public function getScope(){
        return $this->getData('scope');
    }
}