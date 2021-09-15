<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

/**
 * Class CustomerAttributeSourceGroup
 *
 * @author Artem Brunevski
 */

use Magento\Customer\Model\Customer\Attribute\Source\Group;
use Amasty\Perm\Helper\Data as PermHelper;

class CustomerAttributeSourceGroup
{
    /** @var PermHelper  */
    protected $_permHelper;

    /**
     * @param PermHelper $permHelper
     */
    public function __construct(
        PermHelper $permHelper
    ){
        $this->_permHelper = $permHelper;
    }

    /**
     * @param Group $group
     * @param array $options
     * @return array
     */
    public function afterGetAllOptions(
        Group $group,
        $options
    ){
        if ($this->_permHelper->isBackendDealer()) {
            $dealerGroups = $this->_permHelper
                ->getBackendDealer()
                ->getCustomerGroups();
            if (count($dealerGroups) > 0){
                $newOptions = [];
                foreach($options as $option){
                    if (in_array($option['value'], $dealerGroups)){
                        $newOptions[] = $option;
                    }
                }
                $options = $newOptions;
            }
        }
        return $options;
    }
}