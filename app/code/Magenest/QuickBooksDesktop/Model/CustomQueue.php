<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_QuickBooksDesktop extension
 * NOTICE OF LICENSE
 */
namespace Magenest\QuickBooksDesktop\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class CustomQueue
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getId()
 * @method int getTicketId()
 * @method int getCompanyId()
 * @method int getStatus()
 * @method int getIteratorId()
 * @method int getOperation()
 * @method CustomQueue setStatus(int $status)

 */
class CustomQueue extends AbstractModel
{
    /**
     * Initize
     */
    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\CustomQueue');
    }
}
