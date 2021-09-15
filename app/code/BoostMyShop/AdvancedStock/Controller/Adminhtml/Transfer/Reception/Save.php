<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer\Reception;

/**
 * Class Save
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer\Reception
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\TransferFactory
     */
    protected $_transferFactory;

    /**
     * SubmitReception constructor.
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\TransferFactory $transferFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ){
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $warehouseFactory, $uploaderFactory, $fileFactory, $product, $dir);
        $this->_transferFactory = $transferFactory;
    }

    public function execute(){

        try{

            if ($id = filter_var($this->getRequest()->getParam('id'), FILTER_VALIDATE_INT)) {

                $transfer = $this->_transferFactory->create()->load($id);

                if($transfer->getId()){

                    $transfer->processReception($this->_extractData());
                    $this->messageManager->addSuccess(__('Reception saved.'));
                    $this->_redirect('advancedstock/transfer/edit', ['id' => $id]);

                } else {

                    $this->messageManager->addError(__('Transfer no more available.'));
                    $this->_redirect('advancedstock/transfer/index');

                }

            }

        }catch(\Exception $e){

            $this->messageManager->addError(__('An error occurred : %1', $e->getMessage()));
            $this->_redirect('advancedstock/transfer/edit', ['id' => $id]);

        }

    }

    /**
     * @return array $clean
     */
    protected function _extractData(){

        $clean = [];

        foreach($this->getRequest()->getPost('products') as $key => $item){

            if($id = filter_var($key, FILTER_VALIDATE_INT)){

                $clean[$id]['qty'] = filter_var($item['qty'], FILTER_VALIDATE_INT);

            }

        }

        return $clean;

    }

}