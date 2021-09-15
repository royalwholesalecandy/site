<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Wanexo\Brand\Controller\Adminhtml\Brand as BrandController;
use Magento\Framework\Registry;
use Wanexo\Brand\Model\BrandFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

class Edit extends BrandController
{
   
    protected $backendSession;

    protected $resultPageFactory;


    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        BrandFactory $brandFactory,
        BackendSession $backendSession,
        RedirectFactory $resultRedirectFactory,
        Context $context

    )
    {
        $this->backendSession = $backendSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $brandFactory, $resultRedirectFactory, $context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wanexo_Brand::brand');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');
    
        $brand = $this->initBrand();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        //$resultPage->setActiveMenu('Wanexo_Brand::brand');
        $resultPage->getConfig()->getTitle()->set((__('Brand'.' '.$brand->getBrandTitle())));
        if ($id) {
            $brand->load($id);
            if (!$brand->getId()) {
                $this->messageManager->addError(__('This Brand no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'wanexo_brand/*/edit',
                    [
                        'brand_id' => $brand->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $brand->getId() ? $brand->getBrandTitle() : __('New Brand');
        $resultPage->getConfig()->getTitle()->append($title);
        $data = $this->backendSession->getData('wanexo_brand_data', true);
        if (!empty($data)) {
            $brand->setData($data);
        }
        return $resultPage;
    }
}
