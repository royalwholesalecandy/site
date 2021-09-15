<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Wanexo\Mlayer\Controller\Adminhtml\Banner as BannerController;
use Magento\Framework\Registry;
use Wanexo\Mlayer\Model\BannerFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class Edit extends BannerController
{
    /**
     * backend session
     *
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * constructor
     *
     * @param Registry $registry
     * @param BannerFactory $bannerFactory
     * @param BackendSession $backendSession
     * @param PageFactory $resultPageFactory
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        BannerFactory $bannerFactory,
        BackendSession $backendSession,
        RedirectFactory $resultRedirectFactory,
        Date $dateFilter,
        Context $context

    )
    {
        $this->backendSession = $backendSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $bannerFactory, $resultRedirectFactory, $dateFilter, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wanexo_Mlayer::banner');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('banner_id');
        /** @var \Wanexo\Mlayer\Model\Banner $banner */
        $banner = $this->initBanner();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Wanexo_Mlayer::banner');
        $resultPage->getConfig()->getTitle()->set((__('Banners')));
        if ($id) {
            $banner->load($id);
            if (!$banner->getId()) {
                $this->messageManager->addError(__('This banner no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'wanexo_mlayer/*/edit',
                    [
                        'banner_id' => $banner->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $banner->getId() ? $banner->getName() : __('New Banner');
        $resultPage->getConfig()->getTitle()->append($title);
        $data = $this->backendSession->getData('wanexo_mlayer_banner_data', true);
        if (!empty($data)) {
            $banner->setData($data);
        }
        return $resultPage;
    }
}
