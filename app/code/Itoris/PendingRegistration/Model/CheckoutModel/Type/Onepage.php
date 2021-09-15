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

namespace Itoris\PendingRegistration\Model\CheckoutModel\Type;
 
class Onepage extends \Magento\Checkout\Model\Type\Onepage{

    protected function _involveNewCustomer() {
        $customer = $this->getQuote()->getCustomer();

        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
        $helper->init(null);
        $scope = $helper->getFrontendScope();

        if (!\Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope)) {
            return parent::_involveNewCustomer();
        }

        if (!$helper->isEnabled()) {
            return parent::_involveNewCustomer();
        }
        /** @var $db \Magento\Framework\App\ResourceConnection */
        $db = $this->getResourceConnection()->getConnection( 'write' );

        $usersTableName = $this->getResourceConnection()->getTableName( 'itoris_pendingregistration_users' );
        $customersTableName = $this->getResourceConnection()->getTableName( 'customer_entity' );

        $websiteId = (int)$this->_storeManager->getWebsite()->getId();
        $data = $db->fetchRow( 'SELECT * FROM '.$customersTableName.' WHERE email=? and website_id='.$websiteId,
                               $customer->getEmail()  );

        if (isset($data['email'])) {
            $id = intval($data['entity_id']);

            $isExists = (boolean) $db->fetchOne("SELECT COUNT(*) FROM ".$usersTableName." WHERE customer_id=?", $id);
            if (!$isExists) {
                $db->query("INSERT INTO $usersTableName SET customer_id=?, status=0, ip=?, date=CURRENT_TIMESTAMP()",
                            [$id, $_SERVER[ 'REMOTE_ADDR' ]]);

                $user = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Customer')->load($id);

                $helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_ADMIN, $user, $scope);
                $helper->sendEmail(\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_USER,  $user, $scope);

                $this->messageManager->addSuccess(__('Thank You for creating an account with us on RoyalWholesalecandy.com. Your account is pending approval and will be reviewed within 24 hours. You will receive another email once your account is confirmed. Any questions please contact us at 1-888-261-8277 or Chat with us!'));
            }
        }
    }
    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResourceConnection(){
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
    }
    
}