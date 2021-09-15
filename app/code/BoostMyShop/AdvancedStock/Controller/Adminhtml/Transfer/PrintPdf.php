<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintPdf
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrintPdf extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer {

    protected $_transferFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
    ){

        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $warehouseFactory, $uploaderFactory, $fileFactory, $product, $dir);
        $this->_transferFactory = $transferFactory;

    }

    public function execute(){

        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);

        $id = $this->getRequest()->getParam('id');

        if($id = filter_var($id, FILTER_VALIDATE_INT)){

            $transfer = $this->_transferFactory->create()->load($id);

            if($transfer->getId()){

                $pdf = $this->_objectManager->create('BoostMyShop\AdvancedStock\Model\Pdf\Transfer')->getPdf([$transfer]);
                $date = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                    'stock_transfer_'.$date.'.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );

            }else{

                $this->messageManager->addError('Not able to load transfer with id '.$id);
                return $this->resultRedirectFactory->create()->setPath('advancedstock/transfer/index', ['_current' => true]);

            }

        }else{

            $this->messageManager->addError('Bad input');
            return $this->resultRedirectFactory->create()->setPath('advancedstock/transfer/index', ['_current' => true]);

        }

    }

}