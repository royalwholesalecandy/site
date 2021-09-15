<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\ResultFactory;

class SaveWarehouseItem extends \Magento\Backend\App\AbstractAction
{
    protected $_warehouseItemFactory;

    public function __construct(
        Context $context,
        \BoostMyShop\AdvancedStock\Model\Warehouse\ItemFactory $warehouseItemFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_warehouseItemFactory = $warehouseItemFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $data = ($this->getRequest()->getPost('product'));

        $productId = $data['product_id'];

        try
        {
            $advancedStockData = $data['advancedstock'];

            foreach($advancedStockData as $itemId => $itemData)
            {
                $obj = $this->_warehouseItemFactory->create()->load($itemId);

                if (!isset($itemData['wi_use_config_warning_stock_level']))
                    $itemData['wi_use_config_warning_stock_level'] = 0;
                if (!isset($itemData['wi_use_config_ideal_stock_level']))
                    $itemData['wi_use_config_ideal_stock_level'] = 0;

                foreach($itemData as $k => $v)
                    $obj->setData($k, $v);
                $obj->save();
            }

            $this->messageManager->addSuccess(__('Data saved'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError(__('An error occured : %1.', $ex->getTraceAsString()));
        }

        $this->_redirect('catalog/product/edit', ['id' => $productId, 'active_tab' => 'product-advancedstock']);
    }

    protected function _isAllowed()
    {
        return true;
    }

}
