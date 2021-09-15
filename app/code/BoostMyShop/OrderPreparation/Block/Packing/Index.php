<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class Index extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/Index.phtml';

    public function getOrdersInProgress()
    {
        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        return $this->_inProgressFactory->create()->addOrderDetails()->addUserFilter($userId)->addWarehouseFilter($warehouseId);
    }

    public function getSelectOrderByIdUrl()
    {
        return $this->getUrl('*/*/*', ['order_id' => 'param_order_id']);
    }

    public function getSaveItemUrl()
    {
        return $this->getUrl('*/*/saveItem');
    }

    public function getItemCustomOptionsFormUrl()
    {
        return $this->getUrl('*/*/productCustomOptions');
    }

    public function getItemIdsAsJson()
    {
        $ids = array();
        if ($this->hasOrderSelect())
        {
            foreach($this->currentOrderInProgress()->getAllItems() as $item)
            {
                $ids[] = $item->getId();
            }
        }
        return json_encode($ids);
    }

    public function getOrdersAsJson()
    {
        $ids = array();
        foreach($this->getOrdersInProgress() as $item)
        {
            $ids[$item->getId()] = $item->getOrder()->getincrement_id();
        }
        return json_encode($ids);
    }

    public function getMode()
    {
        if (!$this->hasOrderSelect())
            return 'search_order';
        elseif ($this->currentOrderInProgress()->getip_status() == \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_NEW)
            return 'pack_order';
        else
            return 'confirmation';
    }

    public function getAllowPartialPacking()
    {
        return ($this->_config->getAllowPartialPacking() ? 1 : 0);
    }

}