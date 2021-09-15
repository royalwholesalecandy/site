<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Model\Data;

/**
 * Customer group model
 *
 * @api
 * @method string getCustomerGroupCode()
 * @method \Magento\Customer\Model\Group setCustomerGroupCode(string $value)
 * @method \Magento\Customer\Model\Group setTaxClassId(int $value)
 * @method Group setTaxClassName(string $value)
 * @since 100.0.2
 */
class Group extends \Magento\Customer\Model\Data\Group
{
    

   /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setOrderPrefix($prefix)
    {
        return $this->setData('order_prefix', $prefix);
    }

    
}
