<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */

namespace   Magenest\QuickBooksDesktop\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Mapping
 * @package Magenest\QuickBooksDesktop\Model\ResourceModel
 */
class Mapping extends AbstractDb
{
    /**
     * Init
     */
    protected function _construct()
    {
        $this->_init('magenest_qbd_mapping', 'id');
    }
}
