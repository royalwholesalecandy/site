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

class SaveCustomOptions extends Observer
{
    public function execute(\Magento\Framework\Event\Observer $observer){
        /** @var $customerOptions \Itoris\RegFields\Model\Customer */
        $customerOptions = $this->getObjectManager()->create('Itoris\RegFields\Model\Customer');
        $params = $this->session->getItorisParams();
        $customerId = $observer->getCustomer()->getId();
        if($params != false){
            $customerOptions->saveOptions($params, $customerId);
        }
        $this->session->setItorisParams(false);
    }
}