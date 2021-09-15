<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

/**
 * Class Delete
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Delete extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Transfer
     */
    protected $_transferModel;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \BoostMyShop\AdvancedStock\Model\Transfer $transferModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\AdvancedStock\Model\Transfer $transferModel
    ){
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $warehouseFactory, $uploaderFactory, $fileFactory, $product, $dir);
        $this->_transferModel = $transferModel;
    }

    public function execute(){

        $id = $this->getRequest()->getParam('id');

        if($id = filter_var($id, FILTER_VALIDATE_INT)){

            $transfer = $this->_transferModel->load($id);
            $transfer->delete();

        }

        $this->messageManager->addSuccess(__('Transfer successfully deleted'));
        return $this->resultRedirectFactory->create()->setPath('advancedstock/transfer/index', ['_current' => true]);

    }

}