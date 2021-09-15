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
namespace Bss\AddMultipleProducts\Block;

class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    const ZONE_ITEM_LIST = 'item_list';

    /**
     * Flag to indicate the price is for configuration option of a product
     */
    const CONFIGURATION_OPTION_FLAG = 'configuration_option_flag';
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Type\Date
     */
    protected $catalogProductOptionTypeDate;

    /**
     * Product constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->pricingHelper = $pricingHelper;
        $this->stockRegistry = $stockRegistry;
        $this->catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        parent::__construct($context);
    }
    
    /**
     * @param $option
     * @param $product
     * @param $value
     * @param bool $flag
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function formatPrice($option, $product, $value, $flag = true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;
        $resultPage = $this->resultPageFactory->create();
        $customOptionPrice = $product->getPriceInfo()->getPrice('custom_option_price');
        $context = [static::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);
        $priceStr .= $resultPage->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $product
        );
        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }
        return $priceStr;
    }

    /**
     * @param $product
     * @param $_option
     * @param $store
     * @return string
     */
    public function getCustomOptionTextFiled($product, $_option, $store)
    {
        $html = '';
        $_textValidate = null;
        if ($_option->getIsRequire()) {
            $_textValidate['required'] = true;
        }
        if ($_option->getMaxCharacters()) {
            $_textValidate['maxlength'] = $_option->getMaxCharacters();
        }
        $html .='<input type="text"
               id="options_'.$_option->getId().'_text"
               class="input-text product-custom-option"';
        if (!empty($_textValidate)) {
            $html .='data-validate="'.htmlspecialchars(json_encode($_textValidate)).'"';
        }
        if ($this->getCurrentStore($_option, $store) > 0) {
            $html .='price="'.$this->getCurrentStore($_option, $store).'"';
        }
        $html .='name="options['.$_option->getId().']"
               data-selector="options['.$_option->getId().']"
               value="'. htmlspecialchars($product->getPreconfiguredValues()->getData('options/' . $_option->getId())).'"/>';
        return $html;
    }

    /**
     * @param $product
     * @param $_option
     * @param $store
     * @return string
     */
    public function getCustomOptionTextArea($product, $_option, $store)
    {
        $html = '';
        $_textAreaValidate = null;
        if ($_option->getIsRequire()) {
            $_textAreaValidate['required'] = true;
        }
        if ($_option->getMaxCharacters()) {
            $_textAreaValidate['maxlength'] = $_option->getMaxCharacters();
        }
        $html .='<textarea id="options_'.$_option->getId().'_text"
                  class="product-custom-option"';
        if (!empty($_textAreaValidate)) {
            $html .='data-validate="'.htmlspecialchars(json_encode($_textAreaValidate)).'"';
        }
        if ($this->getCurrentStore($_option, $store) > 0) {
            $html .='price="'.$this->getCurrentStore($_option, $store).'"';
        }
        $html .='name="options['.$_option->getId().']"
                  data-selec    tor="options['.$_option->getId().']"
                  rows="5"
                  cols="25">'.htmlspecialchars($product->getPreconfiguredValues()->getData('options/' . $_option->getId())).'</textarea>';
        return $html;
    }

    /**
     * @param $_option
     * @param $store
     * @return float|string
     */
    public function getCurrentStore($_option, $store)
    {
        return $this->pricingHelper->currencyByStore($_option->getPrice(true), $store, false);
    }

    /**
     * @param $price
     * @return float|string
     */
    public function fomatPricePopup($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        $resultPage = $this->resultPageFactory->create();
        $priceRender = $resultPage->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                static::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => static::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * @param $_option
     * @param $product
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDateHtml($_option, $product)
    {
        if ($this->catalogProductOptionTypeDate->useCalendar()) {
            return $this->getCalendarDateHtml($_option, $product);
        } else {
            return $this->getDropDownsDateHtml($_option, $product);
        }
    }

    /**
     * @param $_option
     * @param $product
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCalendarDateHtml($_option, $product)
    {
        $value = $product->getPreconfiguredValues()->getData('options/' . $_option->getId() . '/date');

        $yearStart = $this->catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->catalogProductOptionTypeDate->getYearEnd();

        $calendar = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Date::class
        )->setId(
            'options_' . $_option->getId() . '_date'
        )->setName(
            'options[' . $_option->getId() . '][date]'
        )->setClass(
            'product-custom-option datetime-picker input-text'
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }

    /**
     * @param $_option
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDropDownsDateHtml($_option, $product)
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = $this->catalogProductOptionTypeDate->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml($_option, $product, 'month', 1, 12);
        $daysHtml = $this->_getSelectFromToHtml($_option, $product, 'day', 1, 31);

        $yearStart = $this->catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->catalogProductOptionTypeDate->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml($_option, $product, 'year', $yearStart, $yearEnd);

        $translations = ['d' => $daysHtml, 'm' => $monthsHtml, 'y' => $yearsHtml];
        return strtr($fieldsOrder, $translations);
    }

    /**
     * @param $_option
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTimeHtml($_option, $product)
    {
        if ($this->catalogProductOptionTypeDate->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect(
                $_option,
                $product,
                'day_part'
            )->setOptions(
                ['am' => __('AM'), 'pm' => __('PM')]
            )->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml($_option, $product, 'hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml($_option, $product, 'minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }

    /**
     * @param $_option
     * @param $product
     * @param $name
     * @param $from
     * @param $to
     * @param null $value
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getSelectFromToHtml($_option, $product, $name, $from, $to, $value = null)
    {
        $options = [['value' => '', 'label' => '-']];
        for ($i = $from; $i <= $to; $i++) {
            $options[] = ['value' => $i, 'label' => $this->_getValueWithLeadingZeros($i)];
        }
        return $this->_getHtmlSelect($_option, $product, $name, $value)->setOptions($options)->getHtml();
    }

    /**
     * @param $_option
     * @param $product
     * @param $name
     * @param null $value
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getHtmlSelect($_option, $product, $name, $value = null)
    {
        $require = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setId(
            'options_' . $_option->getId() . '_' . $name
        )->setClass(
            'product-custom-option admin__control-select datetime-picker' . $require
        )->setExtraParams()->setName(
            'options[' . $_option->getId() . '][' . $name . ']'
        );

        $extraParams = 'style="width:auto"';

        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        if ($_option->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }

        $select->setExtraParams($extraParams);
        if ($value === null) {
            $value = $product->getPreconfiguredValues()->getData(
                'options/' . $_option->getId() . '/' . $name
            );
        }
        if ($value !== null) {
            $select->setValue($value);
        }
        return $select;
    }

    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators($product)
    {
        $validators = $params = [];
        $validators['required-number'] = true;

        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );

        $params['minAllowed']  = (float)$stockItem->getMinSaleQty();
        if ($stockItem->getQtyMaxAllowed()) {
            $params['maxAllowed'] = $stockItem->getQtyMaxAllowed();
        }
        if ($stockItem->getQtyIncrements() > 0) {
            $params['qtyIncrements'] = (float)$stockItem->getQtyIncrements();
        }
        $validators['validate-item-quantity'] = $params;

        return $validators;
    }
}
