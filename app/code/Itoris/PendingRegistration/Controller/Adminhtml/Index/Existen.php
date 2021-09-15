<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

//app/code/Itoris/PendingRegistration/Controller/Adminhtml/Index/Existen.php
namespace Itoris\PendingRegistration\Controller\Adminhtml\Index;

class Existen extends \Itoris\PendingRegistration\Controller\Adminhtml\Index
{
    public function execute(){

        $this->currAction = 'existen';

        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();

        $helper = $this->getDataHelper();

        /** @var $button \Itoris\PendingRegistration\Block\Adminhtml\Dropdown */
        /*
        $button = $layout->createBlock( 'Itoris\PendingRegistration\Block\Adminhtml\Dropdown' );
        $button->setScope($this->scope);
        $layout->getBlock( 'left' )->append( $button );

        /** @var $tabs \Itoris\PendingRegistration\Block\Adminhtml\Tabs */
        /*
        $tabs = $layout->createBlock('Itoris\PendingRegistration\Block\Adminhtml\Tabs');
        $tabs->setScope($this->scope);
        $tabs->initTabs();
        $layout->getBlock( 'left' )->append( $tabs );
        /** @var \Itoris\PendingRegistration\Block\Adminhtml\Existen $existen */
        /*
        $existen = $layout->createBlock( 'Itoris\PendingRegistration\Block\Adminhtml\Existen' );
        $layout->getBlock( 'content' )->append( $existen );
           */
        $this->_view->renderLayout();
    }
}
