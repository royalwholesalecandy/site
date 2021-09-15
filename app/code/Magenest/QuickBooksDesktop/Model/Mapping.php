<?php

namespace Magenest\QuickBooksDesktop\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Mapping
 * @package Magenest\QuickBooksDesktop\Model
 * @method int getId()
 * @method int getType()
 * @method int getCompanyId()
 * @method int getListId()
 * @method int getEditSequence()
 */
class Mapping extends AbstractModel
{

    protected function _construct()
    {
        $this->_init('Magenest\QuickBooksDesktop\Model\ResourceModel\Mapping');
    }
}
