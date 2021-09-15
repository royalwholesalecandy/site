<?php

namespace BoostMyShop\AdvancedStock\Controller\Adminhtml;

abstract class ErpProduct extends \Magento\Backend\App\AbstractAction
{
    protected $_coreRegistry;
    protected $_resultLayoutFactory;
    protected $_productFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_productFactory = $productFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();

        $id = $this->getRequest()->getParam('product_id');
        $model = $this->_productFactory->create();
        $model->load($id);
        $this->_coreRegistry->register('current_product', $model);

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
