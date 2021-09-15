<?php
namespace Wanexo\Brand\Controller\Adminhtml\Brandaction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Index extends Action
{
    protected $resultPageFactory;

    protected $resultPage;

  
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->setPageData();
        return $this->getResultPage();
    }

    public function getResultPage()
    {
        if (is_null($this->resultPage)) {
            $this->resultPage = $this->resultPageFactory->create();
        }
        return $this->resultPage;
    }

    protected function setPageData()
    {
        $resultPage = $this->getResultPage();
        //$resultPage->setActiveMenu('Wanexo_Brand::sub_menu');
        $resultPage->getConfig()->getTitle()->set((__('Brand Admin'))); //use id from menu.xml for setActiveMenu
        return $this;
    }
}
