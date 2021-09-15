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

namespace Itoris\PendingRegistration\Observers;

use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;

abstract class Observer implements \Magento\Framework\Event\ObserverInterface
{
    protected $_objectManager;
    protected $customerExtractor;
    protected $subscriberFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ){
        $this->subscriberFactory = $subscriberFactory;
        $this->customerExtractor = $customerExtractor;
        $this->_objectManager = $objectManagerInterface;
        if(!$this->getDataHelper()->isEnabled()){
            return true;
        }
    }
    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    protected function getResourceConnection(){
        return $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession(){
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    protected function getMessageManager(){
        return $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');
    }

    /**
     * @return \Magento\Framework\App\Response\Http
     */
    protected function getResponse(){
        return $this->_objectManager->get('Magento\Framework\App\Response\Http');
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function getUrlInterface(){
        return $this->_objectManager->create('Magento\Framework\UrlInterface');
    }

    /**
     * @return \Itoris\PendingRegistration\Helper\Data
     */
    public function getDataHelper() {
        return $this->_objectManager->create('Itoris\PendingRegistration\Helper\Data');
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource(){
        return $this->_objectManager->get('Magento\Framework\Model\ResourceModel\Db\AbstractDb');
    }
    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest(){
        return $this->_objectManager->get('Magento\Framework\App\RequestInterface');
    }

    /**
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function getSession(){
        return $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public function getLocalDate(){
        return $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry(){
        return $this->_objectManager->create('Magento\Framework\Registry');
    }

    /**
     * @return \Magento\Framework\DataObject\Copy\Config
     */
    public function getConfig(){
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\DataObject\Copy\Config');
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager(){
        return $this->_objectManager->create('Magento\Framework\Event\ManagerInterface');
    }
    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager(){
        return $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
    }
    /**
     * @return \Magento\Customer\Model\Url
     */
    public function getCustomerUrl(){
        return $this->_objectManager->create('Magento\Customer\Model\Url');
    }
}