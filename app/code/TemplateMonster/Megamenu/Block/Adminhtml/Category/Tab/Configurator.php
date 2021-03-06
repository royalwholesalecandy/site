<?php
namespace TemplateMonster\Megamenu\Block\Adminhtml\Category\Tab;

use \Magento\Backend\Helper\Data as BackandHelper;

class Configurator extends \Magento\Backend\Block\Template
{
    const CONFIGURATOR_TEMPLATE = 'TemplateMonster_Megamenu::category/tab/configurator.phtml';

    const FILED_NAME = 'mm_configurator';

    protected $_staticBlocksSource;

    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $helperBackend;

    public function __construct(
        BackandHelper $helperBackend,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Category\Attribute\Source\Page $staticBlocksSource,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperBackend = $helperBackend;
        $this->registry = $registry;
        $this->_staticBlocksSource = $staticBlocksSource;
    }

    public function getTemplate(){
        return self::CONFIGURATOR_TEMPLATE;
    }

    public function toHtml()
    {
        return $this->_toHtml();
    }

    public function getStaticBlocksJson()
    {
        $blocks = $this->_staticBlocksSource->getAllOptions();
        array_shift($blocks);
        return addslashes(\Zend_Json::encode($blocks));
    }

    public function getFieldName()
    {
        return self::FILED_NAME;
    }

    public function getCategory()
    {
        return $this->registry->registry('category');
    }

    public function getConfiguredValue()
    {
        return $this->getCategory()->getMmConfigurator();
    }

    public function getCategoryLevel()
    {
        return $this->getCategory()->getLevel();
    }

    public function getAdminUrl()
    {
        echo $this->helperBackend->getHomePageUrl();
    }
}