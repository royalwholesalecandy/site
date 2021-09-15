<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model;

class Dealer extends \Magento\Framework\Model\AbstractModel
{
    protected $_dealerCustomerFactory;
    protected $_helper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Perm\Model\DealerCustomerFactory $dealerCustomerFactory,
        \Amasty\Perm\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dealerCustomerFactory = $dealerCustomerFactory;
        $this->_helper = $helper;

        return parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Amasty\Perm\Model\ResourceModel\Dealer');
    }

    /**
     * @return array
     */
    public function getCustomersIds()
    {
        $ids = [];
        foreach($this->_getDealerCustomers() as $dealerCustomer){
            $ids[] = $dealerCustomer->getId();
        }
        return $ids;
    }

    protected function _getDealerCustomers()
    {
        $collection = $this->_dealerCustomerFactory->create()
            ->getCollection()
            ->addFieldToFilter('dealer_id', ['eq' => $this->getId()]);

        $items = [];

        foreach($collection as $dealerCustomer){
            $items[$dealerCustomer->getKey()] = $dealerCustomer;
        }

        return $items;
    }

    function saveCustomers(array $customersIds, $removeUnselected = true)
    {
        $ids = [];

        $dealerCustomers = $this->_getDealerCustomers();

        foreach($customersIds as $customerId){
            $dealerCustomerObject = $this->_dealerCustomerFactory->create();

            $dealerCustomerObject
                ->setCustomerId($customerId)
                ->setDealerId($this->getId());

            if (array_key_exists($dealerCustomerObject->getKey(), $dealerCustomers)) {
                $dealerCustomerObject = $dealerCustomers[$dealerCustomerObject->getKey()];
            }

            $dealerCustomerObject->save();
            $ids[] = $dealerCustomerObject->getId();
        }

        if ($removeUnselected){
            $removeCollection = $this->_dealerCustomerFactory->create()
                ->getCollection()
                ->addFieldToFilter('dealer_id', $this->getId());

            if (count($ids) > 0){
                $removeCollection->addFieldToFilter('entity_id', ['nin' => $ids]);
            }

            foreach($removeCollection as $dealerCustomer){
                $dealerCustomer->delete();
            }
        }

        if ($this->_helper->isSingleDealerMode()){
            foreach($this->_dealerCustomerFactory->create()
                ->getCollection()
                ->addFieldToFilter('dealer_id', ['neq' => $this->getId()])
                ->addFieldToFilter('customer_id', ['in' => $customersIds]) as $dealerCustomer){
                $dealerCustomer->delete();
            }
        }
    }

    public function checkPermissions()
    {
        $ret = false;

        if ($this->getId() > 0){
            $collection = $this->getCollection()
                ->addUserData()
                ->addFieldToFilter('main_table.entity_id', $this->getId());

            $ret = $collection->getSize() > 0;

            if ($ret){
                $items = $collection->getItems();
                $item = end($items);
                $this->setContactname($item->getContactname());
                $this->setEmail($item->getEmail());
            }
        }

        return $ret;
    }

    public function getCustomerGroups()
    {
        return $this->getCustomerGroupIds() !== null ? explode(',', $this->getCustomerGroupIds()) : [];
    }

    public function setEmail($email)
    {
        return parent::setEmail($email);
    }

    public function setContactname($name)
    {
        return parent::setContactname($name);
    }

    public function getAllEmails()
    {
        $emails = [];

        if ($this->getEmail() !== null){
            $emails = array_merge($emails, [$this->getEmail()]);
        }

        if ($this->getEmails() !== null){
            $emails = array_merge($emails, explode(',', $this->getEmails()));
        }

        return $emails;
    }

    public function getAllEmailsWithName()
    {
        $list = [];

        foreach($this->getAllEmails() as $email){
            $list[] = [
                'email' => $email,
                'name' => $this->getContactname()
            ];
        }

        return $list;
    }
}