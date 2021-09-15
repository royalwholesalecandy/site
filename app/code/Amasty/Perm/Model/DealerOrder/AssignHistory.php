<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\DealerOrder;

use Amasty\Perm\Helper\Data as PermHelper;
use Amasty\Perm\Model\Mailer;

class AssignHistory extends \Magento\Framework\Model\AbstractModel
{
    protected $_permHelper;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        PermHelper $permHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_permHelper = $permHelper;
        return parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Amasty\Perm\Model\ResourceModel\DealerOrder\AssignHistory');
    }

    public function isDealerChanged()
    {
        return $this->getFromDealerId() != $this->getToDealerId();
    }

    public function getAdminContactname()
    {
        return $this->_permHelper->getScopeValue(Mailer::SCOPE_MESSAGES_ADMIN_NAME);
    }

    public function getAuthorDealerContactname()
    {
        return $this->getAuthorDealerId() ? parent::getAuthorDealerContactname() : $this->getAdminContactname();
    }

    public function getFromDealerContactname()
    {
        return $this->getFromDealerId() ? parent::getFromDealerContactname() : $this->getAdminContactname();
    }

    public function getToDealerContactname()
    {
        return $this->getToDealerId() ? parent::getToDealerContactname() : $this->getAdminContactname();
    }
}