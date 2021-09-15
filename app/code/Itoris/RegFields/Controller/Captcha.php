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

namespace Itoris\RegFields\Controller;

abstract class Captcha extends \Magento\Framework\App\Action\Action
{
    /**
     * @return \Magento\Framework\Filesystem
     */
    public function getSystemFile(){
        return $this->_objectManager->create('Magento\Framework\Filesystem');
    }

    public function getAbsoluteRootPath(){
        $rootDir = $this->getSystemFile()->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        return $rootDir->getAbsolutePath();
    }

    /**
     * @return \Itoris\RegFields\Helper\Data
     */
    public function getDataHelper(){
        return \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
    }
}