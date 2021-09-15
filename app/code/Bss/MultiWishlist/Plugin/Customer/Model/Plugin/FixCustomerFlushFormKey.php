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
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\MultiWishlist\Plugin\Customer\Model\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
use Magento\PageCache\Observer\FlushFormKey;

/**
 * Class FixCustomerFlushFormKey
 * @package Bss\MultiWishlist\Plugin\Customer\Model\Plugin
 */
class FixCustomerFlushFormKey extends \Magento\Customer\Model\Plugin\CustomerFlushFormKey
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var DataFormKey
     */
    private $dataFormKey;

    /**
     * Initialize dependencies.
     *
     * @param Session $session
     */
    public function __construct(Session $session, DataFormKey $dataFormKey)
    {
        $this->session = $session;
        $this->dataFormKey = $dataFormKey;
        parent::__construct($session, $dataFormKey);
    }

    /**
     * @param FlushFormKey $subject
     * @param callable $proceed
     * @param mixed ...$args
     */
    public function aroundExecute(FlushFormKey $subject, callable $proceed, ...$args)
    {
        $currentFormKey = $this->dataFormKey->getFormKey();
        $proceed(...$args);
        $beforeParams = $this->session->getBeforeRequestParams();
        if (isset($beforeParams['form_key']) && $beforeParams['form_key'] == $currentFormKey) {
            $beforeParams['form_key'] = $this->dataFormKey->getFormKey();
            $this->session->setBeforeRequestParams($beforeParams);
        }
    }
}
