<?php

namespace BoostMyShop\Organizer\Controller\Adminhtml\Organizer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class RefreshList extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $layoutFactory;
    protected $_translateInline;
    protected $_organizerFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \BoostMyShop\Organizer\Model\OrganizerFactory $organizerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_translateInline = $translateInline;
        $this->_organizerFactory = $organizerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    
    public function execute()
    {
        $this->_view->loadLayout();
        if ($this->getRequest()->isAjax()) 
        {
            $data = $this->getRequest()->getPost();
            $obj_type = $this->getRequest()->getParam('obj_type');
            $obj_id = $this->getRequest()->getParam('obj_id');
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock('BoostMyShop\Organizer\Block\Organizer\Grid')
                        ->setOrganizerContext($obj_type, $obj_id)
                        ->setTemplate('Organizer/Grid.phtml');

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->getResponse()->setBody($block->toHtml());
            return;
        }
    }
}
?>