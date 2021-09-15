<?php

namespace BoostMyShop\Organizer\Model\Organizer;

class Notification
{
    protected $_config;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_state;
    protected $_userFactory;
    protected $_objectType;

    public function __construct(
        \BoostMyShop\Organizer\Model\Config $config,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userFactory,
        \BoostMyShop\Organizer\Model\ObjectType $objectType
    )
    {
        $this->_config = $config;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_state = $state;
        $this->_userFactory = $userFactory;
        $this->_objectType = $objectType;
    }

    public function notifyToTarget($organizer)
    {

        $assignId = $organizer->geto_assign_to_user_id();
        $email = $this->getAuthor($assignId)->getEmail();
        $name = $this->getAuthor($assignId)->getName();

        
        if (!$email)
            throw new \Exception('No email configured for this user');

        $storeId = 0;
        $sender = $this->_config->getSetting('email_notification/email_identity');
        $template = $this->_config->getSetting('email_notification/email_template');

        $params = $this->buildParams($organizer);

        $this->_sendEmailTemplate($template, $sender, $params, $storeId, $email, $name);
    }

    protected function _sendEmailTemplate($template, $sender, $templateParams = [], $storeId, $recipientEmail, $recipientName)
    {

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $sender
        )->addTo(
            $recipientEmail,
            $recipientName
        );


        $transport= $this->_transportBuilder->getTransport();

        $transport->sendMessage();

        return $this;
    }

    protected function buildParams($organizer)
    {
        $datas = [];

        $datas['task_link'] = $this->_objectType->getObjectUrl($organizer->geto_object_type(), $organizer->geto_object_id());
        $datas['object_name'] = $organizer->geto_object_description();
        $datas['author'] = $this->getAuthor($organizer->geto_author_user_id())->getName();
        $datas['subject'] = $organizer->geto_title();

        $datas['comments'] = $organizer->geto_comments();

        return $datas;
    }

    
    protected function getAuthor($userId)
    {
        return $this->_userFactory->create()->load($userId);
    }
}