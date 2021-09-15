<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintPdf
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrintPdf extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try {

            $id = $this->getRequest()->getParam('id');

            if ($id = filter_var($id, FILTER_VALIDATE_INT)) {

                $stockTake = $this->_stockTakeFactory->create()->load($id);

                if ($stockTake->getId()) {

                    $pdf = $this->_objectManager->create('BoostMyShop\AdvancedStock\Model\Pdf\StockTake')->getPdf([$stockTake]);
                    $date = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                    return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                        'stock_take_' . $date . '.pdf',
                        $pdf->render(),
                        DirectoryList::VAR_DIR,
                        'application/pdf'
                    );

                } else {

                    $this->messageManager->addError('Not able to load stock take with id ' . $id);
                    return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index', ['_current' => true]);

                }

            } else {

                $this->messageManager->addError('Bad input');
                return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index', ['_current' => true]);

            }

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

}