<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\AmazonMagentoConnect\Api\OrderMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Order;

class MassDelete extends Order
{
    /**
     * OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @param Context                     $context
     * @param OrdermapRepositoryInterface $orderMapRepository
     */
    public function __construct(
        Context $context,
        OrderMapRepositoryInterface $orderMapRepository,
        \Magento\Sales\Model\Order $order
    ) {
        $this->orderMapRepository = $orderMapRepository;
        $this->order = $order;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $orderColl = $this->orderMapRepository
                    ->getCollectionByIds($params['orderEntityIds']);

        $orderDeleted = 0;
        $orderIds = [];
        foreach ($orderColl->getItems() as $orderMap) {
            $orderIds[] =  $orderMap->getMageOrderId();
            $orderMap->delete();
            ++$orderDeleted;
        }
        $orders = $this->order->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$orderIds]);

        foreach ($orders as $order) {
            $order->delete();
        }

        $this->messageManager->addSuccess(
            __("A total of %1 record(s) have been deleted.", $orderDeleted)
        );

        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath(
            '*/accounts/edit',
            [
                'id'=>$params['account_id'],
                'active_tab' => 'order_sync'
            ]
        );
    }
}
