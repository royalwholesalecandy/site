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

//app/code/Itoris/PendingRegistration/Controller/Adminhtml/Index/ToggleEngineScope.php
namespace Itoris\PendingRegistration\Controller\Adminhtml\Index;

class ToggleEngineScope extends \Itoris\PendingRegistration\Controller\Adminhtml\Index
{
    public function execute(){

        $action = $this->getRequest()->getParam('action');

        try{
            if(in_array($action, ['activate', 'deactivate'])){
                $value = $action == 'activate' ? true : false;

                /** @var $sett \Itoris\PendingRegistration\Model\Settings */
                $sett = $this->_objectManager->create('Itoris\PendingRegistration\Model\Settings');
                $tightScope = $this->scope->getTightScope();

                if ($value) {
                    $sett->load('active', 'name', $tightScope);
                    $sett->delete();
                } else {
                    $sett->load('active','name', $this->scope);
                    $sett->unsetData('id');
                    $sett->setScope($this->scope->getTightScope());
                }
                $sett->save();

            }else{
                throw new \Exception(__("Invalid action."));
            }
        }catch(\Exception $e){
            $this->getLogger()->critical($e);
            $this->getMessageManager()->addError($e->getMessage());
        }

        $backUrl = $this->getRequest()->getParam('back_url');
        /** @var \Magento\Framework\Url\DecoderInterface $urlDecoder */
        $urlDecoder = $this->_objectManager->create('Magento\Framework\Url\DecoderInterface');
        $backUrl = $urlDecoder->decode($backUrl);
        $this->getResponse()->setRedirect($backUrl);
    }
}