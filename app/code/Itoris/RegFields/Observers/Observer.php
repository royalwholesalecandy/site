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
 * @package    ITORIS_M2_REGISTRATION_FIELDS_MANAGER
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\RegFields\Observers;

abstract class Observer implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Magento\Framework\Registry  */
    protected $_coreRegister;
    /** @var \Magento\Backend\Model\Session  */
    protected $session;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Session $session
    ){
        $this->session = $session;
        $this->_coreRegister = $coreRegistry;
    }
    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager(){
        return $this->getRegFieldsHelper()->getObjectManager();
    }
    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    protected function getRegFieldsHelper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
    }
}