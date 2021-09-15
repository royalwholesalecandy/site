<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Registry;
use Webkul\AmazonMagentoConnect\Model\PriceRuleFactory;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

class Edit extends PriceRule
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

    /**
     * @var \Webkul\AmazonMagentoConnect\Model\PriceRuleFactory
     */
    private $priceRuleFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $registry,
        PriceRuleFactory $priceRuleFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->priceRuleFactory = $priceRuleFactory;
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
        $resultPage->setActiveMenu('Webkul_AmazonMagentoConnect::price_rule')
            ->addBreadcrumb(__('Lists'), __('Lists'))
            ->addBreadcrumb(__('Manage Info'), __('Manage Info'));
        return $resultPage;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $amazonPriceRuleModel=$this->priceRuleFactory->create();
        if ($id) {
            $amazonPriceRuleModel->load($id);
            if (!$amazonPriceRuleModel->getEntityId()) {
                $this->messageManager->addError(__('This Amazon price rule no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->coreRegistry->register('amazon_pricerule', $amazonPriceRuleModel);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Info') : __('New Info'),
            $id ? __('Edit info') : __('New Info')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ?__('Edit Price Rule') : __('New Price Rule'));

        return $resultPage;
    }
}
