<?php

namespace BoostMyShop\OrderPreparation\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    protected $_collectionFactory;
    /**
     * Order config
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_orderConfig = $orderConfig;
    }

    public function toOptionArray()
    {
        $states = $this->_orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_PROCESSING);
        return $states;
    }
}
