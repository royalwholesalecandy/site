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
 * @package    ITORIS_M2_REGISTRATION_FIELDS_MANAGER
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\RegFields\Controller\Adminhtml\Customer;

class ImportAll extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {        
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('write');
        $importFile = $this->getRequest()->getFiles()->get('customer_ireg_import');
        $n = 0; $total = 0;
        if (!empty($importFile)) {
            if (($handle = fopen($importFile['tmp_name'], "r")) !== FALSE) {
                $keys = fgetcsv($handle, 0, ",");
                if (in_array('_customer_email', $keys)) {
                    while (($_data = fgetcsv($handle, 0, ",")) !== FALSE) {
                        $data = [];
                        foreach($keys as $index => $key) $data[$key] = $_data[$index];
                        $total++;
                        if (isset($data['_customer_email'])) {
                            $customerId = (int)$con->fetchOne("select `entity_id` from {$res->getTableName('customer_entity')} where `email` = ".$con->quote($data['_customer_email']));
                            if ($customerId) {
                                $con->query("delete from {$res->getTableName('itoris_regfields_customer_options')} where `customer_id`={$customerId}");
                                foreach($keys as $key) if ($key != '_customer_email' && isset($data[$key]) && !empty($data[$key])) {
                                   $con->query("insert into {$res->getTableName('itoris_regfields_customer_options')} set `customer_id`={$customerId}, `key`=".$con->quote($key).', `value`='.$con->quote($data[$key])); 
                                }
                                $n++;
                            }
                        }
                    }
                }
                fclose($handle);
            }
            $this->messageManager->addSuccess(__('%1 or %2 customers processed successfully', $n, $total));
        } else {
            $this->messageManager->addError(__('Error uploading file'));
        }        
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}