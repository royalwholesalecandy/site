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
namespace Itoris\PendingRegistration\Ui\Component;

class DataProvider extends \Magento\Customer\Model\Customer\DataProvider
{
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $parentData = (array) parent::getData();
        foreach($parentData as $key=>$customer){
            $usersTableName = $this->getResourceConnection()->getTableName('itoris_pendingregistration_users');
            $db = $this->getResourceConnection()->getConnection( 'write' );
            /** @var \Magento\Customer\Model\Customer $customerModel */
            $customerModel = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Customer');
            $currentCustomer = $customerModel->load($customer['customer']['entity_id']);
            $email = $currentCustomer->getEmail();
            $result = $db->query( 'SELECT * FROM '.$usersTableName.' WHERE customer_id='.$db->quote( $currentCustomer->getId() ) );
            $data = $result->fetch();
            if (!$data) {
                /** @var $helper \Itoris\PendingRegistration\Helper\Data */
                $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
                $scope = $helper->getCustomerScope($currentCustomer);
                $status = \Itoris\PendingRegistration\Model\Settings::inst()->isEngineActive($scope) ? \Itoris\PendingRegistration\Model\Users::STATUS_PENDING : \Itoris\PendingRegistration\Model\Users::STATUS_APPROVED;
                $db->query( 'INSERT INTO `'.$usersTableName.'` SET customer_id='.$db->quote($currentCustomer->getId()).', status=' . $status );
                $result = $db->query( 'SELECT status FROM '.$usersTableName.' WHERE customer_id='.$db->quote( $currentCustomer->getId() ) );
                $data = $result->fetch();
            }
            $parentData[$key]['account_admin']['status'] = $data['status'];
        }
        return $parentData;
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResourceConnection(){
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
    }
}