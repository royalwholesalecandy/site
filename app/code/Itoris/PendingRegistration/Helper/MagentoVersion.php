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

namespace Itoris\PendingRegistration\Helper;


class MagentoVersion extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_version;
    /**
     * @return string
     */
    public function getMagentoVersion(){
        if(!$this->_version) {
            /* @var \Magento\Framework\App\ProductMetadata $productMetadata */
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $this->_objectManager->create('Magento\Framework\App\ProductMetadata');
            $vers=explode('.',$productMetadata->getVersion());
            $vers=$vers[0]+($vers[1]/10);
            $this->_version = $vers;
        }

        return $this->_version;
    }
}