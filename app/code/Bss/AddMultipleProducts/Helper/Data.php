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
 * @package    Bss_AddMultipleProducts
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AddMultipleProducts\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $productImageHelper;

    /**
     * @var \Magento\Tax\Model\CalculationFactory
     */
    protected $calculationFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Image $productImageHelper,
        \Magento\Tax\Model\CalculationFactory $calculationFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productImageHelper = $productImageHelper;
        $this->calculationFactory = $calculationFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $this->scopeConfig);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|bool
     */
    public function getCustomerGroup()
    {
        $customer_group = $this->scopeConfig->getValue('ajaxmuntiplecart/general/active_for_customer_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($customer_group != '') {
            return explode(',', $customer_group);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function displayAddmunltiple()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/general/display_addmuntiple', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function defaultQty()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/general/default_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function positionButton()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/button_grid/position_button', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function showTotal()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/button_grid/display_total', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function showSelectProduct()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/button_grid/show_select_product', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function showStick()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/button_grid/show_stick', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function backGroundStick()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/button_grid/background_stick', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowProductImage()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/success_popup/product_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getImageSizesg()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/product_image_size_sg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getImageSizemt()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/product_image_size_mt', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getImageSizeer()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/product_image_size_er', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getItemonslide()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/item_on_slide', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSlidemove()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/slide_move', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSlidespeed()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/slide_speed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSlideauto()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/slide_auto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowProductPrice()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/success_popup/product_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowContinueBtn()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/success_popup/continue_button', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCountDownActive()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/active_countdown', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getCountDownTime()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/countdown_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowCartInfo()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/success_popup/mini_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowCheckoutLink()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/success_popup/mini_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowSuggestBlock()
    {
        return $this->scopeConfig->isSetFlag('ajaxmuntiplecart/success_popup/suggest_product', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSuggestLimit()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup/suggest_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed|string
     */
    public function getBtnTextColor()
    {
        $color = $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/button_text_color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $color = ($color == '') ? '' : $color;
        return $color;
    }

    /**
     * @return mixed
     */
    public function getBtnContinueText()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/continue_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed|string
     */
    public function getBtnContinueBackground()
    {
        $backGround = $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/continue', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $backGround = ($backGround == '') ? '' : $backGround;
        return $backGround;
    }

    /**
     * @return mixed|string
     */
    public function getBtnContinueHover()
    {
        $hover = $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/continue_hover', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $hover = ($hover == '') ? '' : $hover;
        return $hover;
    }

    /**
     * @return mixed
     */
    public function getBtnViewcartText()
    {
        return $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/viewcart_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed|string
     */
    public function getBtnViewcartBackground()
    {
        $backGround = $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/viewcart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $backGround = ($backGround == '') ? '' : $backGround;
        return $backGround;
    }

    /**
     * @return mixed|string
     */
    public function getBtnViewcartHover()
    {
        $hover = $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/viewcart_hover', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $hover = ($hover == '') ? '' : $hover;
        return $hover;
    }

    /**
     * @return mixed|string
     */
    public function getTextbuttonaddmt()
    {
        $button_text_addmt = $this->scopeConfig->getValue('ajaxmuntiplecart/success_popup_design/button_text_addmt', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $button_text_addmt = ($button_text_addmt == '') ? '' : $button_text_addmt;
        return $button_text_addmt;
    }

    /**
     * @param $product
     * @param $imageId
     * @param $size
     * @return \Magento\Catalog\Helper\Image
     */
    public function resizeImage($product, $imageId, $size)
    {
        $resizedImage = $this->productImageHelper
                           ->init($product, $imageId)
                           ->constrainOnly(true)
                           ->keepAspectRatio(true)
                           ->keepTransparency(true)
                           ->keepFrame(false)
                           ->resize($size, $size);
        return $resizedImage;
    }

    /**
     * @param $store
     * @param $taxClassId
     * @return float
     */
    public function getPercent($store, $taxClassId)
    {
        $taxCalculation = $this->calculationFactory->create();
        $request = $taxCalculation->getRateRequest(null, null, null, $store);
        $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
        return $percent;
    }

    /**
     * @param $product
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function taxRate($product)
    {
        $store = $this->storeManager->getStore();
        $taxClassId = $product->getTaxClassId();
        $percent = $this->getPercent($store, $taxClassId);
        return ($percent/100);
    }
}
