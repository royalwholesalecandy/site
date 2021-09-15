<?php

namespace BoostMyShop\Organizer\Controller\Adminhtml\Organizer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $layoutFactory;
    protected $_translateInline;
    protected $_organizerFactory;
    protected $notification;
    protected $authSession;
    protected $date;
    
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
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \BoostMyShop\Organizer\Model\Organizer\Notification $notification,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_translateInline = $translateInline;
        $this->_organizerFactory = $organizerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->notification = $notification;
        $this->date = $date;
        $this->authSession = $authSession;
    }

    
    public function execute()
    {
        $this->_view->loadLayout();
        if ($this->getRequest()->isAjax()) 
        {

            $status = true;
            $msg = __('Task saved');
            try {
                $data = $this->getRequest()->getPost();
                $id = $this->getRequest()->getParam('o_id');
                $date = $this->date->gmtDate();

                $model = $this->_organizerFactory->create()->load($id);
                $model->seto_author_user_id($this->getUserId())
                        ->seto_assign_to_user_id($this->getRequest()->getPost('o_assign_to_user_id'))
                        ->seto_title($this->getRequest()->getPost('o_title'))
                        ->seto_comments($this->getRequest()->getPost('o_comments'))
                        ->seto_category($this->getRequest()->getPost('o_category'))
                        ->seto_priority($this->getRequest()->getPost('o_priority'))
                        ->seto_status($this->getRequest()->getPost('o_status'))
                        ->seto_object_type($this->getRequest()->getPost('o_object_type'))
                        ->seto_object_id($this->getRequest()->getPost('o_object_id'))
                        ->seto_object_description($this->getRequest()->getPost('o_object_description'))
                        ->seto_due_date($this->getRequest()->getPost('o_due_date'))
                        ->seto_updated($date);

                /* Notify to target */
                if(array_key_exists('notify', $this->getRequest()->getParams()) && $this->getRequest()->getParam('notify') == 1){
                    if($this->getRequest()->getPost('o_assign_to_user_id') == ''){
                        $msg = __('There is no assignee assign to this task.');
                    } else {
                        $this->notification->notifyToTarget($model);
                        $model->seto_notified_at($date);
                    }
                }

                if ($id == '')
                    $model->seto_created_at($date);
                $model->save();

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

    protected function getUserId()
    {
        return $this->authSession->getUser()->getId();
    }
}
?>