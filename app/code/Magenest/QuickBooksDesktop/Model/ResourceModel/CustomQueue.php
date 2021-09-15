<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace   Magenest\QuickBooksDesktop\Model\ResourceModel;

/**
 * Class CustomQueue
 * @package Magenest\QuickBooksDesktop\Model\ResourceModel
 */
class CustomQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Init
     */
    protected function _construct()
    {
        $this->_init('magenest_qbd_custom_queue', 'id');
    }
}
