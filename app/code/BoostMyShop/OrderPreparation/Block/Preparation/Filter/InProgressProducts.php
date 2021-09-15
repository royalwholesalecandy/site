<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Filter;

class InProgressProducts extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $_inProgressItemCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\Item\CollectionFactory $inProgressItemCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $resourceHelper, $data);

        $this->_inProgressItemCollectionFactory = $inProgressItemCollectionFactory;
    }


    public function getCondition()
    {

        $value = $this->getValue();
        if (!$value) {
            return null;
        }

        $collection = $this->_inProgressItemCollectionFactory->create()->joinOrderItem();
        $collection->addSearchProductFilter($value);

        $orderIds = $collection->getOrderIds();

        return ['in' => $orderIds];
    }
}
