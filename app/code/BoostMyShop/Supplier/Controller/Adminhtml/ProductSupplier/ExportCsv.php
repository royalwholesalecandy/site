<?php namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportCsv
 *
 * @package   BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportCsv extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier {

    /**
     * @return mixed
     */
    public function execute(){

        try{

            $csv = $this->_view->getLayout()->createBlock('\BoostMyShop\Supplier\Block\ProductSupplier\Grid')->getCsv();

            $date = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');

            return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                'product_supplier_' . $date . '.csv',
                $csv,
                DirectoryList::VAR_DIR,
                'application/csv'
            );

        }catch(\Exception $e){
            $this->messageManager->addError(__('An error occurred : '.$e->getMessage()));
            $this->_redirect('*/*/index');
        }

    }

}