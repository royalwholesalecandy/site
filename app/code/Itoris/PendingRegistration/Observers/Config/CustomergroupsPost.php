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

//app/code/Itoris/PendingRegistration/Observers/Config/CustomergroupsPost.php
namespace Itoris\PendingRegistration\Observers\Config;


class CustomergroupsPost extends \Itoris\PendingRegistration\Observers\Observer
{

    public function execute(\Magento\Framework\Event\Observer $observer){
        $storeCode = $this->getStoreManager()->getStore()->getId();
        $websiteCode = $this->getStoreManager()->getWebsite()->getId();
        try {
            $storeId = $this->getStoreManager()->getStore($storeCode)->getId();
            $websiteId = $this->getStoreManager()->getWebsite($websiteCode)->getId();
            $groups = $this->getRequest()->getParam('groups');
            $customerGroups = $groups['general']['fields']['customer_groups']['value'];
            $this->_objectManager->create('Itoris\PendingRegistration\Model\CustomerGroup')->saveGroups($customerGroups, $websiteId, $storeId, $this->getRequest()->getParam('use_default_customer_group'));
            $this->getMessageManager()->addSuccess(__('Customer Groups have been saved'));
        } catch (\Exception $e) {
            $this->getLogger()->critical($e);
            $this->getMessageManager()->addError(__('Customer Groups have not been saved'));
        }

    }
}