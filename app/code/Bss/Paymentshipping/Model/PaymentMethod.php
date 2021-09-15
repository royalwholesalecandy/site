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
namespace Bss\Paymentshipping\Model;

/**
 * Payment method class.
 */
class PaymentMethod
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @param string $code
     * @param string $title
     * @param int $storeId
     * @param bool $isActive
     */
    public function __construct($code, $title, $storeId, $isActive)
    {
        $this->code = $code;
        $this->title = $title;
        $this->storeId = $storeId;
        $this->isActive = $isActive;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
