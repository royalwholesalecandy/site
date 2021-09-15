<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\Config\Source;

use Amasty\Perm\Model\ResourceModel\Dealer\CollectionFactory;
use Amasty\Perm\Helper\Data as PermHelper;
use Amasty\Perm\Model\Mailer;

class Dealers implements \Magento\Framework\Option\ArrayInterface
{
    protected $_collectionFactory;
    protected $_permHelper;

    public function __construct(
        CollectionFactory $collectionFactory,
        PermHelper $permHelper
    ){
        $this->_permHelper = $permHelper;
        $this->_collectionFactory = $collectionFactory;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray($withAdmin = false)
    {
        $optionArray = [];
        $arr = $this->toArray($withAdmin);
        foreach($arr as $value => $label){
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray($withAdmin = false, array $allowedDealers = [])
    {
        $collection = $this->_collectionFactory->create()->addUserData();

        $options = array();

        if ($withAdmin){
            $options[] = __($this->_permHelper->getScopeValue(Mailer::SCOPE_MESSAGES_ADMIN_NAME));
        }

        foreach($collection as $dealer)
        {
            if (count($allowedDealers) === 0 || in_array($dealer->getId(), $allowedDealers)){
                $options[$dealer->getId()] = $dealer->getContactname();
            }
        }


        return $options;
    }
}
