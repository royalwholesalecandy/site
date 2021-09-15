<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model;

use Amasty\Perm\Model\DealerOrder\AssignHistoryFactory;
use Amasty\Perm\Helper\Data as PermHelper;
use Amasty\Perm\Model\Dealer;
use Amasty\Perm\Model\DealerOrder\AssignHistory;

class DealerOrder extends \Magento\Framework\Model\AbstractModel
{
    /** @var AssignHistoryFactory  */
    protected $_assignHistoryFactory;

    /** @var DealerFactory  */
    protected $_dealerFactory;

    /** @var  Dealer */
    protected $_dealer;

    /** @var PermHelper  */
    protected $_permHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AssignHistoryFactory $assignHistoryFactory
     * @param DealerFactory $dealerFactory
     * @param PermHelper $permHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AssignHistoryFactory $assignHistoryFactory,
        DealerFactory $dealerFactory,
        PermHelper $permHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_assignHistoryFactory = $assignHistoryFactory;
        $this->_dealerFactory = $dealerFactory;
        $this->_permHelper = $permHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Perm\Model\ResourceModel\DealerOrder');
    }

    /**
     * @param $comment
     * @param $orderId
     * @param $toDealerId
     * @return AssignHistory
     */
    public function addDealerHistoryComment($comment, $orderId, $toDealerId)
    {
        $dealer = $this->_dealerFactory->create()->load($toDealerId);
        if (!$dealer->checkPermissions()){
            $dealer = $this->_dealerFactory->create(); //assign to admin
        }

        $history = $this->_assignHistoryFactory->create()
            ->setParentId($orderId)
            ->setToDealerId($dealer->getId())
            ->setToDealerContactname($dealer->getContactname())
            ->setFromDealerId($this->getDealer()->getId())
            ->setFromDealerContactname($this->getDealer()->getContactname())
            ->setComment($comment);

        if ($this->_permHelper->isBackendDealer())
        {
            $history->setAuthorDealerId($this->_permHelper->getBackendDealer()->getId());
            $history->setAuthorDealerContactname($this->_permHelper->getBackendDealer()->getContactname());
        }

        $this->setOrderId($orderId);
        $this->setDealerId($dealer->getId());
        $this->setContactname($dealer->getContactname());

        return $history;
    }

    /**
     * @param bool|false $forceReload
     * @return $this|\Amasty\Perm\Model\Dealer
     */
    public function getDealer($forceReload = false)
    {
        if ($this->_dealer === null || $forceReload){
            $dealer = $this->_dealerFactory->create()->load($this->getDealerId());
            if ($dealer->checkPermissions()){
                $this->_dealer = $dealer;
            } else {
                $this->_dealer = $this->_dealerFactory->create()
                    ->setContactname($this->_permHelper->getScopeValue(Mailer::SCOPE_MESSAGES_ADMIN_NAME))
                    ->setEmails($this->_permHelper->getScopeValue(Mailer::SCOPE_MESSAGES_ADMIN_EMAIL));
            }
        }
        return $this->_dealer;
    }

    /**
     * @return mixed
     */
    public function getAssignHistoryCollection()
    {
        $collection = $this->_assignHistoryFactory->create()
            ->getCollection()
            ->setDealerOrderFilter($this)
            ->setOrder('created_at', 'desc')
            ->setOrder('entity_id', 'desc');

        return $collection;
    }
}