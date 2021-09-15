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

//app/code/Itoris/PendingRegistration/Controller/Adminhtml/Index/ExistenPost.php
namespace Itoris\PendingRegistration\Controller\Adminhtml\Index;

class ExistenPost extends \Itoris\PendingRegistration\Controller\Adminhtml\Index
{
    public function execute(){
        $helper = $this->getDataHelper();
        $status = $this->getRequest()->getParam( 'status' );
        //$email_to_user = $this->getRequest()->getParam( 'email_to_user' );
        if ($status != 'pending' && $status != 'active') {
            $this->getMessageManager()->addError(__('Please select value!') );
            $this->_redirect( '*/*/existen' );
            return;
        }

        $status = $status == 'pending' ? 0 : 1;

        $db = $this->getResourceConnection()->getConnection( 'write' );
        $usersTableName = $this->getResourceConnection()->getTableName( 'itoris_pendingregistration_users' );
        $customerTableName = $this->getResourceConnection()->getTableName( 'customer_entity' );
        $result = $db->query( 'SELECT entity_id FROM '.$customerTableName );
        $customers = $result->fetchAll();
        $cCnt = count( $customers );

        for( $i=0; $i<$cCnt; $i++ )
        {
            $cid = intval( $customers[ $i ][ 'entity_id' ] );
            $result = $db->query( 'SELECT COUNT(*) FROM '.$usersTableName.' WHERE customer_id='.$cid );
            if( !$result->fetchColumn( 0 ) )
            {
                $db->query( 'INSERT INTO '.$usersTableName.' SET customer_id='.$cid.', date=CURRENT_TIMESTAMP(), status='.$status );
            }
            else
            {
                $db->query( 'UPDATE '.$usersTableName.' SET status='.$status );
            }
        }
        if( $status == 0 /*&& $email_to_user */)
        {
            //if( $helper->isCanSendEmail( $helper::IPR_EMAIL_REG_TO_USER, $this->scope ) )
            //{
                for( $i=0; $i<$cCnt; $i++ )
                {
                    $user = $this->_objectManager->create('Magento\Customer\Model\Customer')->load( intval( $customers[ $i ][ 'entity_id' ] ) );
                    $helper->sendEmail( $helper::IPR_EMAIL_REG_TO_USER, $user, $this->scope );
                }
                $this->getMessageManager()->addSuccess(__('Emails successfully sent!'));
            //}
            //else
            //{
                //$this->getMessageManager()->addError(__('Can\'t send emails. Email template not activated or not configured properly!') );
            //}
        }

        $this->getMessageManager()->addSuccess(__('Statuses successfully changed!'));
        //$this->_redirect( '*/*/existen' );
    }
}