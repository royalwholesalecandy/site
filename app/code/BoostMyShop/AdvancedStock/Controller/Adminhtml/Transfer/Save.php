<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

/**
 * Class Save
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Save extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Transfer
     */
    protected $_transferModel;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Transfer\ItemFactory
     */
    protected $_itemTransferModelFactory;

    /**
     * Save constructor.
     * @param \BoostMyShop\AdvancedStock\Model\Transfer $transferModel
     * @param \BoostMyShop\AdvancedStock\Model\Transfer\ItemFactory $itemTransferModelFactory
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
        \BoostMyShop\AdvancedStock\Model\Transfer $transferModel,
        \BoostMyShop\AdvancedStock\Model\Transfer\ItemFactory $itemTransferModelFactory,
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
        $this->_transferModel = $transferModel;
        $this->_itemTransferModelFactory = $itemTransferModelFactory;
    }

    public function execute(){

        $clean = $this->_extractData();

        if(!empty($clean['st_id'])){

            $this->_updateRecord($clean);

        }else{

            $this->_insertNewRecord($clean);

        }

        $this->messageManager->addSuccess(__('Transfer successfully saved'));
        return $this->_createResultFactory();
    }

    /**
     * @return array $clean
     */
    protected function _extractData(){

        $data = $this->getRequest()->getParam('transfer');

        $clean = filter_var_array(
            $data,
            [
                'st_reference' => FILTER_SANITIZE_STRING,
                'st_from' => FILTER_VALIDATE_INT,
                'st_to' => FILTER_VALIDATE_INT,
                'st_website_id' => FILTER_VALIDATE_INT,
                'st_status' => FILTER_SANITIZE_STRING,
                'st_notes' => FILTER_SANITIZE_STRING,
                'st_id' => FILTER_VALIDATE_INT
            ]
        );

        if(isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $productId => $qties) {

                if ($productId = filter_var($productId, FILTER_VALIDATE_INT)) {

                    if (isset($qties['qty_to_transfer']) && $value = filter_var($qties['qty_to_transfer'], FILTER_VALIDATE_INT)) {

                        $clean['products'][$productId]['qty_to_transfer'] = $value;

                    }

                }

            }
        }

        if(isset($data['delete']) && is_array($data['delete'])) {
            foreach ($data['delete'] as $stiId => $value) {

                if (($id = filter_var($stiId, FILTER_VALIDATE_INT)) && ($value = filter_var($value, FILTER_SANITIZE_STRING))) {

                    $clean['delete'][$id] = $value;

                }

            }
        }

        return $clean;

    }

    /**
     * @param $clean array
     */
    protected function _updateRecord($clean){

        $transfer = $this->_transferModel->load($clean['st_id'])
            ->setData('st_reference', $clean['st_reference'])
            ->setData('st_from', $clean['st_from'])
            ->setData('st_to', $clean['st_to'])
            ->setData('st_status', $clean['st_status'])
            ->setData('st_website_id', $clean['st_website_id'])
            ->setData('st_notes', $clean['st_notes'])
            ->save();

        foreach($transfer->getItems() as $item){

            if (isset($clean['delete'][$item->getsti_id()])) {

                $item->getTransferItem()->delete();

            } else {

                $qtyToTransfer = (isset($clean['products'][$item->getst_product_id()]['qty_to_transfer'])) ? $clean['products'][$item->getst_product_id()]['qty_to_transfer'] : null;

                if(!is_null($qtyToTransfer)) {
                    $item->getTransferItem()->setData('st_qty', $qtyToTransfer);
                }

                if($item->getTransferItem()->getOrigData('st_qty') != $item->getTransferItem()->getData('st_qty')) {
                    $item->getTransferItem()->save();
                }

            }

            unset($clean['products'][$item->getst_product_id()]);

        }

        if(isset($clean['products']) && is_array($clean['products'])) {
            foreach ($clean['products'] as $productId => $qties) {

                $this->_itemTransferModelFactory->create()
                    ->setData('st_transfer_id', $this->_transferModel->getId())
                    ->setData('st_product_id', $productId)
                    ->setData('st_qty_transfered', 0)
                    ->setData('st_qty', isset($qties['qty_to_transfer']) ? $qties['qty_to_transfer'] : 0)
                    ->save();

            }
        }

    }

    /**
     * @param $clean array
     */
    protected function _insertNewRecord($clean){

        $this->_transferModel
            ->setData('st_reference', $clean['st_reference'])
            ->setData('st_from', $clean['st_from'])
            ->setData('st_to', $clean['st_to'])
            ->setData('st_status', $clean['st_status'])
            ->setData('st_website_id', $clean['st_website_id'])
            ->setData('st_notes', $clean['st_notes'])
            ->save();

        if(isset($clean['products']) && is_array($clean['products'])) {

            foreach ($clean['products'] as $productId => $qties) {

                $this->_itemTransferModelFactory->create()
                    ->setData('st_transfer_id', $this->_transferModel->getId())
                    ->setData('st_product_id', $productId)
                    ->setData('st_qty', isset($qties['qty_to_transfer']) ? $qties['qty_to_transfer'] : 0)
                    ->setst_qty_transfered(0)
                    ->save();

            }

        }

    }

    protected function _createResultFactory(){

        if ($this->getRequest()->getParam('back') == 'edit') {

            return $this->resultRedirectFactory->create()->setPath('advancedstock/transfer/edit', ['_current' => true, 'id' => $this->_transferModel->getId()]);

        } elseif ($this->getRequest()->getParam('back') == 'new') {

            return $this->resultRedirectFactory->create()->setPath('advancedstock/transfer/edit');

        } else {

            return $this->resultRedirectFactory->create()->setPath('advancedstock/transfer/index', ['_current' => true]);

        }

    }

}