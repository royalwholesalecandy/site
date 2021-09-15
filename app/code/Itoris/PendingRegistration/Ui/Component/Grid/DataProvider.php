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
namespace Itoris\PendingRegistration\Ui\Component\Grid;

class DataProvider extends \Magento\Customer\Ui\Component\DataProvider
{
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = parent::getData();
        /** @var $helper \Itoris\PendingRegistration\Helper\Data */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
        $helper->init(null);
        $resource = $this->getResourceConnection();
        $db = $resource->getConnection('write');
        $usersTableName = $resource->getTableName('itoris_pendingregistration_users');
        $customerTableName = $resource->getTableName('customer_entity');
        $db->query( 'DELETE FROM '.$usersTableName.' WHERE customer_id=0' );
        $result = $db->query( 'SELECT entity_id FROM '.$customerTableName );
        $customers = $result->fetchAll();
        $cCnt = count($customers);
        for($i=0; $i<$cCnt; $i++) {
            $cid = intval( $customers[ $i ][ 'entity_id' ] );
            $result = $db->query( 'SELECT COUNT(*) FROM '.$usersTableName.' WHERE customer_id='.$cid );

            if (!$result->fetchColumn( 0 )) {
                $db->query( 'INSERT INTO '.$usersTableName.' SET customer_id='.$cid.', status=1' );
            }

        }
        foreach($data['items'] as $key=>$item){
            $result = $db->query( 'SELECT * FROM '.$usersTableName.' WHERE customer_id='.$item['entity_id'] );
            $customer = $result->fetch();
            $data['items'][$key]['status'] = $customer['status'];
        }
        return $data;
    }


    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResourceConnection(){
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
    }

}
