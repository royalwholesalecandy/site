<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Ui\Component\Listing\Column\Website;

use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Options constructor.
     * @param StoreManager $storeManager
     */
    public function __construct(StoreManager $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->getWebsitesArray() as $item) {
            $result[$item->getId()] = $item->getName();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getWebsitesArray() as $item) {
            $result[] = [
                'value' => $item->getId(),
                'label' => $item->getName()
            ];
        }

        return $result;
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    protected function getWebsitesArray()
    {
        return $this->storeManager->getWebsites();
    }
}
