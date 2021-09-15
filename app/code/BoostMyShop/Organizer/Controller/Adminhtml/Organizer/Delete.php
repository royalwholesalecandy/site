<?php

namespace BoostMyShop\Organizer\Controller\Adminhtml\Organizer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Backend\App\Action
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
            $status = true;
            $msg = __('Task saved');
            try {
                $id = $this->getRequest()->getParam('o_id');
                
                $model = $this->_organizerFactory->create()->load($id);
                $model->delete();
                $status = true;
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                $status = false;
            }

            $response = array(
                'error' => (!$status),
                'message' => $msg
            );

            $result = $this->resultJsonFactory->create();
            return $result->setData($response);
        }
    }
}
?>