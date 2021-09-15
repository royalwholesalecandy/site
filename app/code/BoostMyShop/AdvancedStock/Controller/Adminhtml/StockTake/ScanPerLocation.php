<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class ScanPerLocation
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScanPerLocation extends  \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try {

            $this->_registerCurrentStockTake();

            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Scan Location'));
            $this->_view->renderLayout();

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

}