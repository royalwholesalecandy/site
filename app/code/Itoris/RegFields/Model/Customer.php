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

namespace Itoris\RegFields\Model;
use Magento\Framework\DataObject\IdentityInterface;

class Customer extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'regfields_customer_tag';

    public function _construct() {
        $this->_init('Itoris\RegFields\Model\ResourceModel\Customer');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    /**
     * Save customer custom fields values
     *
     * @param $options
     * @param $customerId
     * @return mixed
     */
    public function saveOptions($options, $customerId) {
        if (empty($options)) {
            return;
        }
        $read = $this->getReadConnection();
        $write = $this->getResource()->getConnection();
		//clean up
		$keys = array_keys($options);
		$keys[] = 'dummy_key';
		foreach($keys as & $key) $key = "'{$key}'";
        $write->query("DELETE FROM ".$this->getResource()->getMainTable()." WHERE `key` NOT IN (".implode(',', $keys).") AND customer_id='".$customerId."'");
		
		//updating options
        foreach($options as $key => $option) {
            if (!($key == 'password' || $key == 'confirmation')) {
                if (!empty($option)) {
                    if (is_array($option)) {
                        $error = false;
                        foreach ($option as $item) {
                            if (is_array($item)) {
                                $error = true;
                                break;
                            }
                        }
                        if (!$error) {
                            $value = implode(',', $option);
                        }
                    } else {
                        $value = $option;
                    }
                    if ($value == 'itoris_field_has_file') {
                        continue;
                    }
                    $select = $read->select()
                                   ->from($this->getResource()->getMainTable())
                                   ->where('`customer_id`=?', $customerId, \Zend_Db::INT_TYPE)
                                   ->where('`key`=?', $key);
                    $data = $read->fetchRow($select);
                    if ($data) {
                        $this->setData($data);
                        $method = 'update';
                    } else {
                        $this->setCustomerId($customerId);
                        $this->setKey($key);
                        $method = 'insert';
                    }
                    $this->setValue($value);
                    if($method == 'update'){
                        $write->query("UPDATE ".$this->getResource()->getMainTable()." SET `value`='".$this->getValue()."' WHERE `key`='".$this->getKey()."' AND customer_id='".$customerId."'");
                    }elseif($method == 'insert'){
                        $write->query( "INSERT INTO ".$this->getResource()->getMainTable()." SET customer_id=".$customerId.", `key` ='". $this->getKey()."', `value` ='".$this->getValue()."'");
                    }
                    $method = false;
                    //$this->save();
                    $this->unsetData();
                }
            }
        }
    }

    /**
     * Load custom fields values for customer
     *
     * @param $key
     * @param $customerId
     * @return mixed
     */
    public function loadOption($key, $customerId) {
        $read = $this->getReadConnection();
        $select = $read->select()
                       ->from($this->getResource()->getMainTable())
                       ->where('`customer_id`=?', $customerId, \Zend_Db::INT_TYPE)
                       ->where('`key`=?', $key);
        $data = $read->fetchRow($select);
        if ($data) {
            $this->setData($data);
        }
        return $this->getValue();
    }

    public function loadOptionsByCustomerId($customerId) {
        $read = $this->getReadConnection();
        $select = $read->select()
            ->from($this->getResource()->getMainTable())
            ->where('`customer_id`=?', $customerId, \Zend_Db::INT_TYPE);

        $values = $read->fetchAll($select);
        $result = [];
        foreach ($values as $value) {
            $result[$value['key']] = $value['value'];
        }

        return $result;
    }

    /**
     * Add custom options to customer model
     *
     * @param $customer \Magento\Customer\Model\Customer
     * @return \Itoris\RegFields\Model\Customer
     */
    public function addOptionsToCustomer($customer) {
        if ($customer->getId()) {
            $options = $this->loadOptionsByCustomerId($customer->getId());
            foreach ($options as $code => $value) {
                if (!$customer->getData($code)) {
                    $customer->setData($code, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    public function getFieldsHelper(){
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadConnection(){
        return $this->getFieldsHelper()->getResourceConnection()->getConnection('read');
    }

}
