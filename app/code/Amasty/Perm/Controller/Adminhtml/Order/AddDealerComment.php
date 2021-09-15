<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Perm\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Amasty\Perm\Model\DealerOrder;
use Amasty\Perm\Model\DealerOrder\AssignHistory;

class AddDealerComment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /** @var  $dealerOrder*/
    protected $_dealerOrder;

    /**
     * @param $orderId
     * @return mixed
     */
    protected function _initDealerOrder($orderId)
    {
        if ($this->_dealerOrder === null){
            $this->_dealerOrder = $this->_objectManager->create('Amasty\Perm\Model\DealerOrder')->load($orderId, 'order_id');
            if (!$this->_dealerOrder->getId()){
                $this->_dealerOrder->setOrderId($orderId);
            }
        }
        return $this->_dealerOrder;
    }

    /**
     * @return $this|\Magento\Framework\Controller\Result\Json|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('amasty_perm_order_dealer_comment_history');

                /** @var  DealerOrder $dealerOrder */
                $dealerOrder = $this->_initDealerOrder($order->getId());

                $emailsList = $dealerOrder->getDealer(true)->getAllEmailsWithName();

                if (empty($data['comment']) && $data['dealer'] == $dealerOrder->getDealerId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_dealer_notified']) ? $data['is_dealer_notified'] : false;

                /** @var AssignHistory $history */
                $history = $dealerOrder->addDealerHistoryComment($data['comment'], $order->getId(), $data['dealer']);

                $history->setIsDealerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));

                $dealerOrder->save();

                $vars = [
                    'order' => $order,
                    'comment' => $comment,
                    'billing' => $order->getBillingAddress(),
                    'store' => $order->getStore(),
                    'history' => $history,
                    'dealerOrder' => $dealerOrder
                ];

                if ($notify) {

                    if ($history->isDealerChanged()){
                        $emailsList = array_merge($emailsList, $dealerOrder->getDealer(true)->getAllEmailsWithName());
                    }

                    $this->_objectManager->create('Amasty\Perm\Model\Mailer')
                        ->send(
                            $order->getStoreId(),
                            $emailsList,
                            $vars
                        );
                }

                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => $e->getMessage() . __('We cannot assign dealer.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}
