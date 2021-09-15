<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\ResourceModel;

class DealerCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Perm\Helper\Data $helper,
        $connectionName = null
    ){
        $this->_helper = $helper;

        return parent::__construct($context, $connectionName);
    }
    protected function _construct()
    {
        $this->_init('amasty_perm_dealer_customer', 'entity_id');
    }

    public function getCustomers(\Amasty\Perm\Model\Dealer $dealer)
    {
        $connection = $this->getConnection();

        $binds = ['dealer_id' => $dealer->getId()];

        $select = $connection->select()
            ->from($this->getMainTable(), ['customer_id'])
            ->where('dealer_id = :dealer_id');

        return $connection->fetchCol($select, $binds);
    }
}