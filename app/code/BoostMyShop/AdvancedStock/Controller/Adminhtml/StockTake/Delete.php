<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class Delete
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Delete extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try {

            $id = $this->getRequest()->getParam('id');

            if ($id = filter_var($id, FILTER_VALIDATE_INT)) {

                $stockTake = $this->_stockTakeFactory->create()->load($id);
                $stockTake->delete();

            }

            $this->messageManager->addSuccess(__('Stock Take successfully deleted'));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index', ['_current' => true]);

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

}