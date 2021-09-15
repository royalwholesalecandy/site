<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Controller\Adminhtml\Dealer;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Amasty\Perm\Model\DealerFactory;

class MassAssign extends AbstractMassAction
{
    protected $redirectUrl = 'customer/index/index';
    protected $dealerFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DealerFactory $dealerFactory
    ){
        parent::__construct($context, $filter, $collectionFactory);
        $this->dealerFactory = $dealerFactory;
    }


    protected function massAction(AbstractCollection $collection)
    {
        $dealer = $this->dealerFactory->create()
            ->load($this->getRequest()->getParam('dealer'));

        $customersIds = array_keys($collection->getItems());

        $dealer->saveCustomers($customersIds, false);

        $customersUpdated = count($customersIds);

        if ($customersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl()?: 'customer/index/index';
    }

}
