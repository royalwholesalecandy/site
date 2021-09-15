<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Filter;

class Products extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $_salesOrderItemCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $salesOrderItemCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $resourceHelper, $data);

        $this->_salesOrderItemCollectionFactory = $salesOrderItemCollectionFactory;
    }


    public function getCondition()
    {

        $value = $this->getValue();
        if (!$value) {
            return null;
        }

        $value = addslashes($value);
        $collection = $this->_salesOrderItemCollectionFactory->create()->addFieldToFilter(['name', 'sku'], [['like' => '%'.$value.'%'], ['like' => '%'.$value.'%']]);
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(new \Zend_Db_Expr('distinct order_id'));
        $orderIds = $collection->getConnection()->fetchCol($collection->getSelect());

        return ['in' => $orderIds];
    }
}
