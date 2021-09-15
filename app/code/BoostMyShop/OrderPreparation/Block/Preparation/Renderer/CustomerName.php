<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Renderer;

use Magento\Framework\DataObject;

class CustomerName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected static $_groups;
    protected $_customerGroupCollectionFactory;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_customerGroupCollectionFactory = $customerGroupCollectionFactory;
    }

    public function render(DataObject $order)
    {
        $html = $order->getshipping_name();

        if ($order->getcustomer_group())
        {
            $html .= '<br><i>('.$this->getGroupName($order->getcustomer_group()).')</i>';
        }

        return $html;
    }

    protected function getGroupName($groupId)
    {
        if (!self::$_groups)
        {
            self::$_groups = [];
            $collection = $this->_customerGroupCollectionFactory->create();
            foreach($collection as $item)
            {
                self::$_groups[$item->getId()] = $item->getcustomer_group_code();
            }
        }

        return (isset(self::$_groups[$groupId]) ? self::$_groups[$groupId] : '');
    }
}