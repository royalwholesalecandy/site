<?php
namespace BoostMyShop\OrderPreparation\Block\Shipping;

class Header extends \Magento\Backend\Block\Template
{
    protected $_template = 'OrderPreparation/Shipping/Header.phtml';

    protected $_coreRegistry = null;
    protected $_preparationRegistry;
    protected $_templateCollectionFactory;
    protected $_config = null;
    protected $_request;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \BoostMyShop\OrderPreparation\Model\Config $config,
                                \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\CarrierTemplate\CollectionFactory $templateCollectionFactory,
                                array $data = [],
                                \Magento\Framework\App\Request\Http $request
    )
    {
        parent::__construct($context, $data);
        $this->_config = $config;
        $this->_coreRegistry = $registry;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_templateCollectionFactory = $templateCollectionFactory;
        $this->_request = $request;
    }

    public function getTemplates()
    {
        return $this->_templateCollectionFactory->create()->addActiveFilter();
    }

    public function getCarrierExportUrl($template)
    {
        return $this->getUrl('*/*/templateExport', ['ct_id' => $template->getId()]);
    }

    public function getImportUrl()
    {
        return $this->getUrl('*/*/templateImport');
    }

}