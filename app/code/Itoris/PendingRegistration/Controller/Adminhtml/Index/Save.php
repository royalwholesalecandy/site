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

//app/code/Itoris/PendingRegistration/Controller/Adminhtml/Index/Save.php
namespace Itoris\PendingRegistration\Controller\Adminhtml\Index;

class Save extends \Itoris\PendingRegistration\Controller\Adminhtml\Index
{
    public function execute(){

        $request = $this->getRequest();
        /** @var $template \Itoris\PendingRegistration\Model\Template */
        $template = $this->_objectManager->create('Itoris\PendingRegistration\Model\Template');
        $templateType = $this->getRequest()->getParam('template');
        if($templateType === null){
            throw new \Exception(__("Unknown template type."));
        }
        $tightScope = $this->scope->getTightScope();
        $template->load($templateType, 'type', $this->scope->getTightScope());

        if(!(bool)$template->getId()){
            $template->setType($templateType);
            $template->setScope($tightScope);
        }
        $isUseDefault = (bool) $this->getRequest()->getParam('is_use_default', false);
        if($isUseDefault){
            $template->delete();
            try{
                $template->save();

                $this->getMessageManager()->addSuccess(__('Template successfully saved!'));
            }catch(\Exception $e){
                $this->getMessageManager()->addError($e->getMessage());
            }
            $this->_redirect( '*/*/index', array_merge( [ 'template' => $templateType ],
                $this->scope->getSummaryForUrl()));
            return;
        }

        $fromName = $request->getParam( 'from_name' );
        $fromEmail = $request->getParam( 'from_email' );
        $adminEmail = $request->getParam( 'admin_email');
        //$subject = $request->getParam( 'subject' );
        $cc = $request->getParam( 'cc' );
        $bcc = $request->getParam( 'bcc' );
        $emailContent = $request->getParam( 'email_content' );
        $emailStyles = $request->getParam( 'email_styles' );
        $isActive = $request->getParam('active');

        if( empty($emailContent) || empty($fromName) || empty($subject) || empty($fromEmail) ){
            $this->getMessageManager()->addError(__('Please fill all required fields!'));
            $this->_redirect( '*/*/index', array_merge( [ 'template' => $templateType ],
                $this->scope->getSummaryForUrl()));
            return;
        }

        $template->setFromName( $fromName );
        $template->setFromEmail( $fromEmail );
        $template->setAdminEmail($adminEmail);
        //$template->setSubject( $subject );
        $template->setCc( $cc );
        $template->setBcc( $bcc );
        $template->setEmailContent( $emailContent );
        $template->setEmailStyles( $emailStyles );
        $template->setActive($isActive);

        try{
            $template->save();
            $this->getMessageManager()->addSuccess(__('Template successfully saved!'));
        }catch(\Exception $e){
            $this->getMessageManager()->addError($e->getMessage());
        }

        $this->_redirect( '*/*/index', array_merge( [ 'template' => $templateType ],
            $this->scope->getSummaryForUrl() ) );
    }
}