<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model\ResourceModel\Toprated;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Sorting\Model\Toprated;
use Amasty\Sorting\Model\ResourceModel\Method\Toprated as TopratedResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            Toprated::class,
            TopratedResource::class
        );
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addIdFilter($ids)
    {
        $this->addFieldToFilter('product_id', ['in' => $ids]);

        return $this;
    }
}
