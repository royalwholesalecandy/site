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

namespace Itoris\PendingRegistration\Model;

class Users extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Data\OptionSourceInterface {

    const STATUS_NOT_CONFIRMED_BY_EMAIL = 5;
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DECLINED = 2;

    protected function _construct() {
        $this->_init( 'Itoris\PendingRegistration\Model\ResourceModel\Users' );
    }

    public function toOptionArray(){
        $values = [
            ['label'=>__('Pending'), 'value'=>self::STATUS_PENDING],
            ['label'=>__('Approved'), 'value'=>self::STATUS_APPROVED],
            ['label'=>__('Declined'), 'value'=>self::STATUS_DECLINED]
        ];
        return $values;
    }
}
