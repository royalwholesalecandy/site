<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\Pocustomize\Model\ResourceModel;
/**
 * Order configuration model
 *
 * @api
 * @since 100.0.2
 */
class Popayment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('potransaction_capture_payment', 'entity_id');
    }
}
