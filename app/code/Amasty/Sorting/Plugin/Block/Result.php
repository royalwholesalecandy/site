<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Plugin\Block;

use Amasty\Sorting\Helper\Data;
use Magento\CatalogSearch\Block\Result as Subject;

class Result
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Subject $result
     * @return $this
     */
    public function afterSetListOrders(Subject $result)
    {
        $result->getListBlock()->setDefaultSortBy(
            $this->helper->getSearchSorting()
        );

        return $this;
    }
}
