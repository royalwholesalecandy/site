<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Reception\Renderer;

use Magento\Framework\DataObject;

class Details extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Reception\Item\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_collectionFactory = $collectionFactory;
    }

    public function render(DataObject $row)
    {
        $html = [];

        try
        {
            $collection = $this->_collectionFactory->create()->addOrderProductDetails($row->getpor_po_id())->addReceptionFilter($row->getId());

            foreach($collection as $item)
            {
                $html[] = $item->getTotalQty().'x '.$item->getpop_sku().' - '.$item->getpop_name();
            }

            return implode('<br>', $html);
        }
        catch(\Exception $ex)
        {
            return '<font color="red">'.$ex->getMessage().'</font>';
        }

    }
}