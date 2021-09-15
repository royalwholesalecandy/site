<?php
namespace BoostMyShop\Supplier\Block\ProductSupplier;

class PopupTabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;

    protected $_productFactory;
    protected $_supplierFactory;


    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('popup_tabs');
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;

        $this->_productFactory = $productFactory;
        $this->_supplierFactory = $supplierFactory;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\Supplier\Block\ProductSupplier\PopupTabs\General')->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
    public function getSaveUrl()
    {
        return $this->getUrl('supplier/productSupplier/save');
    }

    public function getPopupUrl()
    {
        return $this->getUrl('supplier/productSupplier/popup');
    }

    public function getWindowTitle()
    {
        $productId = $this->_coreRegistry->registry('current_popup_productId');
        $supplierId = $this->_coreRegistry->registry('current_popup_supId');

        $product = $this->_productFactory->create()->load($productId);
        $supplier = $this->_supplierFactory->create()->load($supplierId);

        return $supplier->getsup_name().' - '.$product->getName();
    }

}
