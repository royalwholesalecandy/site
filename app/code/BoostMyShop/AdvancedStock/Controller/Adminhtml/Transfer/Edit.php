<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer;

/**
 * Class Edit
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\Transfer {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\TransferFactory
     */
    protected $_transferFactory;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
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
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
    ) {

        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $warehouseFactory, $uploaderFactory, $fileFactory, $product, $dir);
        $this->_transferFactory = $transferFactory;
    }

    public function execute(){

        $this->_registerCurrentTransfer();

        $this->_view->loadLayout();

        if(!is_null($this->_coreRegistry->registry('current_transfer')->getId()))
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Stock Transfer %1', $this->_coreRegistry->registry('current_transfer')->getst_reference()));
        else
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Create new Stock Transfer'));

        $this->_view->renderLayout();

    }

    protected function _registerCurrentTransfer(){

        if($id = filter_var($this->getRequest()->getParam('id'))){

            $this->_coreRegistry->register('current_transfer', $this->_transferFactory->create()->load($id));

        }else{

            $this->_coreRegistry->register('current_transfer', $this->_transferFactory->create());

        }

    }

}