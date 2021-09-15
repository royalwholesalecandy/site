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

//app/code/Itoris/DynamicProductOptions/Controller/Adminhtml/Settings.php
namespace Itoris\PendingRegistration\Controller\Adminhtml;

abstract class Index extends \Magento\Backend\App\Action
{
    protected $currAction = 'index';
    /** @var \Itoris\PendingRegistration\Model\Scope */
    protected $scope = null;

    public function _construct(){
        $this->getDataHelper()->init($this->getRequest());
        if(!$this->getDataHelper()->isEnabled()){
            return true;
        }
    }

    public function preDispatch(){
        //$preDispatch = parent::preDispatch();
        $this->getDataHelper()->tryRegister();
        if(!$this->getDataHelper()->isEnabled()) {
            $this->_actionFlag->get('', self::FLAG_NO_DISPATCH, true);
            $this->getMessageManager()->addError(__('Component not registered!'));
            $this->_view->loadLayout();
            $register = $this->_view->getLayout()->createBlock( 'itoris_pendingregistration/admin_register' );
            $this->_view->getLayout()->getBlock( 'content' )->append( $register );
            $this->_view->renderLayout();
        }

        /** @var $scope \Itoris\PendingRegistration\Model\Scope */
        $scope = $this->_objectManager->create('Itoris\PendingRegistration\Model\Scope');
        $scope->setStoreCode($this->getRequest()->getParam(\Itoris\PendingRegistration\Model\Scope::$CONFIGURATION_SCOPE_STORE));
        $scope->setWebsiteCode($this->getRequest()->getParam(\Itoris\PendingRegistration\Model\Scope::$CONFIGURATION_SCOPE_WEBSITE));
        $this->scope = $scope;

        //return $preDispatch;
    }

    /**
     * @return \Itoris\PendingRegistration\Helper\Data
     */
    protected function getDataHelper(){
        return $this->_objectManager->create('Itoris\PendingRegistration\Helper\Data');
    }

    protected function _isAllowed(){
        return $this->_authorization->isAllowed('Itoris_PendingRegistration::pendingreg');
    }
    /**
     * Create connection adapter instance
     * @return \Magento\Framework\App\ResourceConnection
     */
    protected function getResourceConnection(){
        return $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager(){
        return $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(){
        return $this->_objectManager->create('Psr\Log\LoggerInterface');
    }

}