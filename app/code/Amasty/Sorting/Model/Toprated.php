<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model;

use Magento\Framework\Model\AbstractModel;

class Toprated extends AbstractModel
{
    public function _construct()
    {
        $this->setIdFieldName('product_id');
    }

    /**
     * @param $resourceName
     * @param null $collectionName
     */
    public function setResourceModel($resourceName, $collectionName = null)
    {
        parent::_setResourceModel($resourceName, $collectionName);
    }
}
