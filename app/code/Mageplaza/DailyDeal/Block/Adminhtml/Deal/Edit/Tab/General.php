<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block\Adminhtml\Deal\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class General
 * @package Mageplaza\DailyDeal\Block\Adminhtml\Deal\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'Mageplaza_DailyDeal::deal/general.phtml';

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Mageplaza\DailyDeal\Model\ResourceModel\DealFactory
     */
    protected $_dealFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var Yesno
     */
    protected $_booleanOptions;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    protected $_helperData;

    /**
     * General constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param ProductFactory $productFactory
     * @param PriceHelper $priceHelper
     * @param Yesno $booleanOptions
     * @param StockRegistryInterface $stockRegistry
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        ProductFactory $productFactory,
        PriceHelper $priceHelper,
        Yesno $booleanOptions,
        StockRegistryInterface $stockRegistry,
        HelperData $helperData,
        array $data = []
    )
    {
        $this->_systemStore    = $systemStore;
        $this->_productFactory = $productFactory;
        $this->_priceHelper    = $priceHelper;
        $this->_booleanOptions = $booleanOptions;
        $this->stockRegistry   = $stockRegistry;
        $this->_helperData     = $helperData;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\DailyDeal\Model\Deal $deal */
        $deal = $this->_coreRegistry->registry('mageplaza_dailydeal_deal');

        $productId = $deal->getProductId();
        $sku       = $deal->getProductSku();

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('deal_');
        $form->setFieldNameSuffix('deal');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('General Information'),
            'class'  => 'fieldset-wide'
        ]);
        $fieldset->addField('product_id', 'hidden', [
            'name'  => 'product_id',
            'label' => __('Product Id'),
            'title' => __('Product Id')
        ]);
        $fieldset->addField('product_name', 'text', [
            'name'               => 'product_name',
            'label'              => __('Product'),
            'title'              => __('Product'),
            'after_element_html' => '<a id="load-product-grid" class="btn">' . __('Select Product') . '</a>',
            'readonly'           => true,
            'required'           => true,
            'note'               => __('Select a product which the deal is applied on')
        ]);
        $fieldset->addField('product_sku', 'hidden', [
            'name'  => 'product_sku',
            'label' => __('SKU'),
            'title' => __('SKU')
        ]);
        $fieldset->addField('original_price', 'note', [
            'label' => __('Original Price'),
            'text'  => $this->_storeManager->getStore()->getBaseCurrency()->format($this->_helperData->getProductPrice($productId))
        ]);
        $fieldset->addField('product_qty', 'note', [
            'label' => __('Product Qty'),
            'text'  => $this->_helperData->getProductQty($sku)
        ]);
        $fieldset->addField('status', 'select', [
            'name'     => 'status',
            'label'    => __('Status'),
            'title'    => __('Status'),
            'required' => true,
            'options'  => [
                '1' => __('Active'),
                '0' => __('Inactive')
            ]
        ]);
        $fieldset->addField('is_featured', 'select', [
            'name'   => 'is_featured',
            'label'  => __('Is Featured'),
            'title'  => __('Is Featured'),
            'values' => $this->_booleanOptions->toOptionArray(),
            'note'   => __('If yes, this deal will be shown on Feature Deal Slider')
        ]);
        $fieldset->addField('deal_price', 'text', [
            'name'     => 'deal_price',
            'label'    => __('Deal Price'),
            'title'    => __('Deal Price'),
            'required' => true,
            'class'    => 'validate-number validate-deal-price'
        ]);
        $fieldset->addField('discount', 'note', [
            'label' => __('Discount'),
            'text'  => $this->_priceHelper->currency($this->getPrice($productId), true, false)
        ]);
        $fieldset->addField('deal_qty', 'text', [
            'name'     => 'deal_qty',
            'label'    => __('Deal Qty'),
            'title'    => __('Deal Qty'),
            'required' => true,
            'class'    => 'validate-number validate-deal-qty'
        ]);
        $fieldset->addField('sale_qty_label', 'note', [
            'name'  => 'sale_qty_label',
            'label' => __('Qty of sold items'),
            'title' => __('Qty of sold items')
        ]);
        $fieldset->addField('sale_qty', 'hidden', [
            'name' => 'sale_qty'
        ]);
        if (!$this->_storeManager->isSingleStoreMode()) {
            /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $fieldset->addField('store_ids', 'multiselect', [
                'name'     => 'store_ids',
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'required' => true,
                'values'   => $this->_systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);

            if (!$deal->hasData('store_ids')) {
                $deal->setStoreIds(0);
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name'  => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        }
        $fieldset->addField('date_from', 'date', [
            'name'        => 'date_from',
            'label'       => __('Start On'),
            'title'       => __('Start On'),
            'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
            'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
            'timezone'    => false
        ]);
        $fieldset->addField('date_to', 'date', [
            'name'        => 'date_to',
            'label'       => __('End On'),
            'title'       => __('End On'),
            'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
            'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
            'timezone'    => false
        ]);

        $form->setValues($deal->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get transaction grid url
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpdailydeal/deal/productsgrid');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }
}
