<?php

namespace BoostMyShop\Organizer\Controller\Adminhtml\Organizer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;
    protected $_coreRegistry;
    protected $_translateInline;
    protected $resultJsonFactory; 

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->layoutFactory = $layoutFactory;
        $this->_translateInline = $translateInline;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    
    public function execute()
    {
        $this->_view->loadLayout();
        if ($this->getRequest()->isAjax()) 
        {

            $id = $this->getRequest()->getParam('o_id');
            $objType = $this->getRequest()->getParam('objType');
            $objId = $this->getRequest()->getParam('objId');
            
            $this->_coreRegistry->register('current_popup_orgId', $id);
            
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock('BoostMyShop\Organizer\Block\Organizer\Edit');
            $block->setoId($id);
            $block->setOrganizerContext($objType, $objId);
            $block->setTemplate('Organizer/Edit.phtml');
            $block->setOrganizerContext($objType, $objId);
            
            $result = $this->resultJsonFactory->create();
            $result->setData(['data' => $block->toHtml()]);
            return $result;  
        }
    }
}
?>