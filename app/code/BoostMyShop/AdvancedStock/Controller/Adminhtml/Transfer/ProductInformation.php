<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

class ProductInformation extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
{
    protected $resultJsonFactory;
    protected $_productInfo;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \BoostMyShop\AdvancedStock\Model\ProductInformation $productInfo
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $warehouseFactory, $uploaderFactory, $fileFactory, $product, $dir);
        $this->_productInfo = $productInfo;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $barcode = $this->getRequest()->getParam('barcode');
            if (!$barcode) {
                throw new \Exception(__('Please enter a barcode'));
            }
            $data = $this->_productInfo->getJsonDataForBarcode($barcode);
            $data['success'] = true;
            return $result->setData($data);
        } catch (\Exception $ex) {
            return $result->setData(['success' => false, 'msg' => $ex->getMessage()]);
        }
    }
}
