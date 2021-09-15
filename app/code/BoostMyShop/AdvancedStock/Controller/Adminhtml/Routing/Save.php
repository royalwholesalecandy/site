<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Routing;

class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Routing
{

    /**
     * @return void
     */
    public function execute()
    {
        try
        {
            $datas = $this->getRequest()->getPost('routing');
            foreach($datas as $websiteId => $websiteData)
            {
                foreach($websiteData as $groupId => $groupDatas) {
                    foreach ($groupDatas as $storeId => $storeDatas) {
                        foreach ($storeDatas as $routingType => $fields) {
                            switch ($routingType) {
                                case 'store':
                                    $this->updateRoutingStoreData($websiteId, $groupId, $storeId, $fields);
                                    break;
                                case 'warehouse':
                                    foreach($fields as $warehouseId => $warehouseData)
                                        $this->updateWarehouseStoreData($websiteId, $groupId, $storeId, $warehouseId, $warehouseData);
                                    break;
                            }
                        }
                    }
                }
            }

            $this->messageManager->addSuccess(__('Configuration successfully saved'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1', $ex->getMessage()));
        }

        $this->_redirect('*/*/index');
    }

    protected function updateRoutingStoreData($websiteId, $groupId, $storeId, $fields)
    {
        $obj = $this->_routingStoreFactory->create()->loadByStore($websiteId, $groupId, $storeId);

        //fix checkbox non returning values
        if (!isset($fields['rs_use_default']))
            $fields['rs_use_default'] = 0;

        foreach($fields as $k => $v)
            $obj->setData($k, $v);

        $obj->save();
    }

    protected function updateWarehouseStoreData($websiteId, $groupId, $storeId, $warehouseId, $warehouseData)
    {
        $obj = $this->_routingStoreWarehouse->create()->loadByStoreWarehouse($websiteId, $groupId, $storeId, $warehouseId);

        //fix checkbox non returning values
        if (!isset($warehouseData['rsw_use_default']))
            $warehouseData['rsw_use_default'] = 0;

        foreach($warehouseData as $k => $v)
            $obj->setData($k, $v);

        $obj->save();
    }
}
