<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Amasty\Perm\Model\Dealer;
use Amasty\Perm\Model\DealerCustomerFactory;
use Amasty\Perm\Model\DealerFactory;

class DealerCustomer extends \Magento\Framework\Model\AbstractModel
{
    /** @var  Dealer */
    protected $_dealer;

    /** @var \Amasty\Perm\Model\DealerFactory  */
    protected $_dealerFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Amasty\Perm\Model\DealerFactory $dealerFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DealerFactory $dealerFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dealerFactory = $dealerFactory;

        return parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Perm\Model\ResourceModel\DealerCustomer');
    }

    /**
     * @param \Amasty\Perm\Model\Dealer $dealer
     * @return mixed
     */
    public function getCustomers(Dealer $dealer)
    {
        return $this->getResource()->getCustomers($dealer);
    }

    /**
     * @return mixed
     */
    public function dealerCustomerExists()
    {
        return $this->getResource()->dealerCustomerExists($this);
    }

    /**
     * @return mixed
     */
    public function deleteFromDealer()
    {
        return $this->getResource()->deleteFromDealer($this);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->getCustomerId() . '_' . $this->getDealerId();
    }

    /**
     * @return $this|\Amasty\Perm\Model\Dealer
     */
    public function getDealer()
    {
        if ($this->_dealer === null){
            $this->_dealer = $this->_dealerFactory->create()->load($this->getDealerId());
        }
        return $this->_dealer;
    }
}