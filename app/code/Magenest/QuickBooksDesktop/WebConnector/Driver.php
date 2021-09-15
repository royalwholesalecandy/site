<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace Magenest\QuickBooksDesktop\WebConnector;

use Magenest\QuickBooksDesktop\Model\ResourceModel\Queue\CollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magenest\QuickBooksDesktop\Model\Mapping as Mapping;
use Magenest\QuickBooksDesktop\Model\User as UserModel;
use Psr\Log\LoggerInterface;
use Magenest\QuickBooksDesktop\Helper\CreateQueue as QueueHelper;

/**
 * Class Driver
 * @package Magenest\QuickBooksDesktop\WebConnector
 */
abstract class Driver
{
    /**
     * @var QueueHelper
     */
    protected $_queueHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var UserModel
     */
    protected $_user;

    /**
     * @var Mapping
     */
    protected $_map;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Driver constructor.
     * @param CollectionFactory $collectionFactory
     * @param ObjectManagerInterface $objectManager
     * @param UserModel $user
     * @param Mapping $map
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ObjectManagerInterface $objectManager,
        LoggerInterface $loggerInterface,
        UserModel $user,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Mapping $map,
        QueueHelper $queueHelper
    ) {
        $this->_logger = $loggerInterface;
        $this->_user = $user;
        $this->_map = $map;
        $this->_collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_queueHelper = $queueHelper;
    }

    /**
     * Authenticate Username and password
     *
     * @param \stdClass $obj
     * @return bool
     */
    public function authenticate($obj)
    {
        $username = $obj->strUserName;
        $password = $obj->strPassword;

        $pass = md5($password);
        $model = $this->_user->load($username, 'username');

        if ($model->getId()) {
            $passUser = $model->getPassword();
            $status = $model->getStatus();

            if (($pass == $passUser) && ($status == 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getTotalsQueue()
    {
        //TODO in Children
    }

    /**
     * @return \Magenest\QuickBooksDesktop\Model\Queue
     */
    public function getCurrentQueue()
    {
        //TODO in Children
    }

    /**
     * @param $queue
     * @return string
     */
    public function prepareSendRequestXML($queue)
    {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/inventory_req11.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('testt');
        //TODO in Children
    }

    public function simpleXml($value, $tag)
    {
        if ($value !== '') {
            return "<$tag>$value</$tag>";
        } else {
            return '';
        }
    }

    public function multipleXml($value, array $tags)
    {
        $xml = '';
        if ($value !== '') {
            foreach ($tags as $tag) {
                $xml .= "<$tag>";
            }
            $xml .= "$value";
            $tags = array_reverse($tags);
            foreach ($tags as $tag) {
                $xml .= "</$tag>";
            }
        }
        return $xml;
    }
}
