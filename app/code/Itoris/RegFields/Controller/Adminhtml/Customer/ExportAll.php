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

class ExportAll extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('read');
        $options = $con->fetchAll("select * from {$res->getTableName('itoris_regfields_customer_options')} order by `option_id`");
        $customer_emails = [];
        $csv = [];
        $keys = ['_customer_email' => 1];
        foreach($options as $option) {
            if (!isset($customer_emails[$option['customer_id']])) {
                $customer_emails[$option['customer_id']] =  $con->fetchOne("select `email` from {$res->getTableName('customer_entity')} where `entity_id` = ".$option['customer_id']);
                $csv[$option['customer_id']] = ['_customer_email' => $customer_emails[$option['customer_id']]];
            }
            $keys[$option['key']] = 1;
            $csv[$option['customer_id']][$option['key']] = $option['value'];
        }
        $keys = array_keys($keys);
        foreach($csv as &$line) {
            $_line = [];
            foreach($keys as $key) $_line[] = isset($line[$key]) ? '"'.str_replace('"', '""', $line[$key]).'"' : '';
            $line = implode(',', $_line);
        }
        $str = implode(',', $keys) . "\n" . implode("\n", $csv);
        $this->getResponse()->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', strlen($str), true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . basename('AdditionalRegInfoDump'.date('Y-m-d').'.csv') . '"', true)
            ->setHeader('Last-Modified', date('r'), true)
            ->sendHeaders();
        $this->getResponse()->setBody($str);
    }
}