<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_Paymentshipping
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\Paymentshipping\Plugin;

class PaymentMethodList
{
    protected $bssHelper;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Bss\Paymentshipping\Helper\Data $bssHelper
    ) {
        $this->bssHelper = $bssHelper;
    }

    public function afterGetAvailableMethods(\Magento\Payment\Model\MethodList $subject, $paymentMethodList)
    {
        $myHelperData = $this->bssHelper;

        foreach($paymentMethodList as $key => $method) {
            if (!$myHelperData->canUseMethod($method->getCode(), 'payment')){
                unset($paymentMethodList[$key]);
            }
        }

        return $paymentMethodList;
    }
}
