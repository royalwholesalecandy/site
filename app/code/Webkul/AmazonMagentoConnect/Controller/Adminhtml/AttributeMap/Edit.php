<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\AttributeMap;

use Magento\Framework\Locale\Resolver;
use Webkul\AmazonMagentoConnect\Model\AttributeMapFactory;
use Magento\Framework\Registry;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\AttributeMap;

class Edit extends AttributeMap
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context,
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory,
     * @param AttributeMapFactory $attributeMapFactory,
     * @param Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AttributeMapFactory $attributeMapFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->attributeMapFactory = $attributeMapFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

   /**
    * Init actions
    *
    * @return \Magento\Backend\Model\View\Result\Page
    */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_AmazonMagentoConnect::attribute_map')
            ->addBreadcrumb(__('Lists'), __('Lists'))
            ->addBreadcrumb(__('Manage Attribute map'), __('Manage Attribute map'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $label =  __('Attribute Map');
        $resultPage->addBreadcrumb($label, $label);
        $resultPage->getConfig()->getTitle()->prepend(__('Attribute Map'));
        return $resultPage;
    }
}
