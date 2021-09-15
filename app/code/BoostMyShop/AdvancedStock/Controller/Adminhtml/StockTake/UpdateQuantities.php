<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class UpdateQuantities
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateQuantities extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try{

            if ($id = filter_var($this->getRequest()->getParam('id'), FILTER_VALIDATE_INT)) {

                $stockTake = $this->_stockTakeFactory->create()->load($id);

                if($stockTake->getId()){

                    $stockTake->updateQuantities();

                }

            }

            if (!isset($stockTake) || empty($stockTake->getId())) {

                $this->messageManager->addErrorMessage(__('Not able to load stock take'));
                return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index', ['_current' => true]);

            }

            $this->messageManager->addSuccess(__('Quantities have been successfully updated'));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/edit', ['_current' => true, 'id' => $stockTake->getId()]);

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred'));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

}