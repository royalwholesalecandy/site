<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake\Scan;

/**
 * Class Save
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake\Scan
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try{

            if($id = filter_var($this->getRequest()->getParam('id'), FILTER_VALIDATE_INT)) {

                $stockTake = $this->_stockTakeFactory->create()->load($id);

                if($stockTake->getId()){

                    $data = $this->_extractData();
                    $stockTake->processScan($data);

                }

            }

            if (!isset($stockTake) || empty($stockTake->getId())) {

                $this->messageManager->addErrorMessage(__('Not able to load stock take'));
                return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index', ['_current' => true]);

            }

            $this->messageManager->addSuccess(__('Scan successfully processed'));

            if($this->getRequest()->getParam('saveAndScanLocation') && $stockTake->getsta_status() != \BoostMyShop\AdvancedStock\Model\StockTake::STATUS_COMPLETE){

                return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/scanPerLocation', ['_current' => true, 'id' => $stockTake->getId()]);

            }

            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/edit', ['_current' => true, 'id' => $stockTake->getId()]);

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index', ['_current' => true]);

        }

    }

    /**
     * @return array $clean
     */
    protected function _extractData(){

        $clean = [];

        $products = $this->getRequest()->getPost('products');
        if(!empty($products)) {
            foreach ($products as $sku => $scannedQty) {

                $sku = filter_var($sku, FILTER_SANITIZE_STRING);
                $scannedQty = filter_var($scannedQty['scanned_qty'], FILTER_VALIDATE_INT);

                if (!empty($sku) && !empty($scannedQty)) {

                    $clean[$sku] = $scannedQty;

                }

            }
        }

        return $clean;

    }

}