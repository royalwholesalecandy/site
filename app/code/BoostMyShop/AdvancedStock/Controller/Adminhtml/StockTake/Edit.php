<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class Edit
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try {

            $this->_registerCurrentStockTake();

            $this->_view->loadLayout();

            if (!is_null($this->_coreRegistry->registry('current_stocktake')->getId()))
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Stock Take %1', $this->_coreRegistry->registry('current_stocktake')->getsta_name()));
            else
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Create new Stock Take'));

            $this->_view->renderLayout();

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

}