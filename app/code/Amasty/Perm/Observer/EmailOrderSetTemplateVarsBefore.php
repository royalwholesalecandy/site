<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */


namespace Amasty\Perm\Observer;

use Magento\Framework\Event\ObserverInterface;

class EmailOrderSetTemplateVarsBefore  implements ObserverInterface
{
    /** @var \Psr\Log\LoggerInterface  */
    private $logger;

    /** @var \Amasty\Perm\Model\DealerOrderFactory  */
    private $dealerOrderFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Perm\Model\DealerOrderFactory $dealerOrderFactory
    ) {
        $this->logger = $logger;
        $this->dealerOrderFactory = $dealerOrderFactory;
    }

    /**
     * Add {{var order.getOrderDealer()}} to order, invoice, creditmemo, shipment templates.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\DataObject|array $transport */
        $transport = $observer->getEvent()->getData('transport');
        try {
            if (!isset($transport['order'])) {
                return;
            }
            $order = $transport['order'];
            /** @var \Amasty\Perm\Model\DealerOrder $dealerOrder */
            $dealerOrder = $this->dealerOrderFactory->create()->load($order->getId(), 'order_id');
            if ($dealerOrder->getId()) {
                $order->setOrderDealer($dealerOrder->getDealer());
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
