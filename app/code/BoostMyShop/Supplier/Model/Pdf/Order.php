<?php

namespace BoostMyShop\Supplier\Model\Pdf;


class Order extends AbstractPdf
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    protected $_config;
    protected $_product;

    protected $_skuFeed = 70;
    protected $_nameFeed = 200;

    protected $_priceFeed;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \BoostMyShop\Supplier\Model\ConfigFactory $config,
        \BoostMyShop\Supplier\Model\Product $product,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_config = $config;
        $this->_product = $product;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $data
        );
    }

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;

        if ($this->_config->create()->getSetting('order_product/enable_discount'))
            $this->_priceFeed = 430;
        else
            $this->_priceFeed = 480;

        //columns headers
        $lines[0][] = ['text' => __('Qty'), 'feed' => 45, 'align' => 'right'];
        if($this->_config->create()->getSetting('general/pack_quantity')){
            $this->_skuFeed = 120;
            $lines[0][] = ['text' => __('Pack qty'), 'feed' => 70, 'align' => 'left'];
        }
        $lines[0][] = ['text' => __('SKU'), 'feed' => $this->_skuFeed, 'align' => 'left'];
        $lines[0][] = ['text' => __('Product'), 'feed' => 200, 'align' => 'left'];

        $lines[0][] = ['text' => __('Price'), 'feed' => $this->_priceFeed, 'align' => 'right'];
        if ($this->_config->create()->getSetting('order_product/enable_discount'))
            $lines[0][] = ['text' => __('Discount'), 'feed' => 490, 'align' => 'right'];

        $lines[0][] = ['text' => __('Total'), 'feed' => 550, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($orders = [])
    {
        $this->_beforeGetPdf();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($orders as $order) {

            if ($order->getPoStoreId()) {
                $this->_localeResolver->emulate($order->getPoStoreId());
                $this->_storeManager->setCurrentStore($order->getPoStoreId());
            }
            $page = $this->newPage();

            /* Add image */
            $this->insertLogo($page, $order->getStore());

            /* Add document text and number */
            $this->drawPoInformation($page, $order);

            $this->drawAddresses($page, $order);

            $this->drawPublicComments($page, $order);

            $this->_drawHeader($page);

            /* Add body */
            foreach ($order->getAllItems() as $item) {

                //check available space
                if ($this->y < 100)
                    $page = $this->newPage();

                /* Draw item */
                $this->_drawItem($item, $page, $order);

                $page = end($pdf->pages);
            }

            $this->insertCosts($page, $order);

            $this->insertTotals($page, $order);

            $this->insertAdditionnal($page, $order);

            if ($order->getPoStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    /**
     * @param $item
     * @param $page
     * @param $order
     */
    protected function _drawItem($item, $page, $order)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        //columns headers
        $lines[0][] = ['text' => $item->getPopQty(), 'feed' => 45, 'align' => 'right'];
        if($this->_config->create()->getSetting('general/pack_quantity'))
            $lines[0][] = ['text' => $item->getpop_qty_pack().'x', 'feed' => 70, 'align' => 'left'];


        $lines[0][] = ['text' => $order->getCurrency()->format($item->getPopPrice(), [], false), 'feed' => $this->_priceFeed, 'align' => 'right'];

        if ($this->_config->create()->getSetting('order_product/enable_discount') && ($item->getPopDiscountPercent() > 0))
            $lines[0][] = ['text' => $item->getPopDiscountPercent().'%', 'feed' => 490, 'align' => 'right'];

        $lines[0][] = ['text' => $order->getCurrency()->format($item->getPopSubtotal(), [], false), 'feed' => 550, 'align' => 'right'];
        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y += 5;

        //1) PREPARE DATA

        //SKU
        $skuLines = $this->splitTextToSize($item->getPopSku(), $page->getFont(), 10, 100);

        //SUPPLIER SKU
        if ($item->getPopSupplierSku())
            $supplierSkuLines = $this->splitTextToSize($item->getPopSupplierSku(), $page->getFont(), 10, 100);

        //PRODUCT NAME
        $nameLines = $this->splitTextToSize($item->getPopName(), $page->getFont(), 10, 180);
        $barcode = $this->_product->getBarcode($item->getpop_product_id());
        if ($barcode)
            $nameLines[] = __('Barcode').': '.$barcode;
        $location = $this->_product->getLocation($item->getpop_product_id(), $order->getpo_warehouse_id());
        if ($location)
            $nameLines[] = __('Location').': '.$location;

        //1) DISPLAY DATA

        //top y baseline
        $baseDisplayY = $this->y;
        $interlineHeight = 10;

        //DISPLAY SKU
        foreach($skuLines as $skuLine){
            $page->drawText($skuLine, $this->_skuFeed, $this->y, 'UTF-8');
            $this->y -= $interlineHeight;
        }

        $this->y -= $interlineHeight;

        //DISPLAY SUPPLIER SKU
        if ($item->getPopSupplierSku()) {
            foreach ($supplierSkuLines as $supplierSkuLine) {
                $page->drawText($supplierSkuLine, $this->_skuFeed, $this->y, 'UTF-8');
                $this->y -= $interlineHeight;
            }
        }
        $endDisplayYAfterSku = $this->y;

        //SET GAIN Y BASELINE TO VERTICAL ALIGN
        $this->y = $baseDisplayY;

        //DISPLAY PRODUCT NAME
        foreach($nameLines as $nameLine) {
            $page->drawText($nameLine, $this->_nameFeed, $this->y, 'UTF-8');
            $this->y -= $interlineHeight;
        }
        $endDisplayYAfterName = $this->y;

        //keep the lowest Y to avoid text override
        $this->y = ($endDisplayYAfterSku<$endDisplayYAfterName)?$endDisplayYAfterSku:$endDisplayYAfterName;

        //bottom item margin
        $this->y -= $interlineHeight;
    }

    /**
     * @param $page
     * @param $order
     */
    protected function insertCosts($page, $order)
    {
        $costs = ['Shipping' => $order->getpo_shipping_cost(), 'Additionnal' => $order->getpo_additionnal_cost()];
        foreach($costs as $label => $value)
        {
            if ($value > 0)
            {
                $lines = [];
                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                $lines[0][] = ['text' => $label, 'feed' => 200, 'align' => 'left'];
                $lines[0][] = ['text' => $order->getCurrency()->format($value, [], false), 'feed' => $this->_priceFeed, 'align' => 'right'];
                $lines[0][] = ['text' => $order->getCurrency()->format($value, [], false), 'feed' => 550, 'align' => 'right'];
                $lineBlock = ['lines' => $lines, 'height' => 5];

                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->y -= 20;
            }
        }

    }

    /**
     * Insert totals to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $order)
    {
        $totals = [];
        $totals[] = ['label' => __('Subtotal'), 'value' => $order->getPoSubtotal()];
        if ($order->getGlobalDiscountAmount() > 0)
            $totals[] = ['label' => __('Discount').' ('.$order->getpo_global_discount().'%)', 'value' => $order->getGlobalDiscountAmount()];
        $totals[] = ['label' => __('Shipping & additionnal'), 'value' => $order->getPoShippingCost() + $order->getPoAdditionnalCost()];
        $totals[] = ['label' => __('Taxes'), 'value' => $order->getPoTax()];
        $totals[] = ['label' => __('Grand total'), 'value' => $order->getPoGrandtotal()];

        $page->drawLine(25, $this->y, 570, $this->y);
        $this->y -= 20;

        $this->_setFontBold($page, 18);

        //check available space
        if ($this->y < 100)
            $page = $this->newPage();

        foreach($totals as $total)
        {
            $lines = [];
            $lines[0][] = ['text' => __($total['label']), 'font_size' => 14, 'feed' => 350, 'align' => 'left'];
            $lines[0][] = ['text' => $order->getCurrency()->format($total['value'], [], false), 'font_size' => 14, 'feed' => 550, 'align' => 'right'];
            $lineBlock = ['lines' => $lines, 'height' => 20];
            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        }

        return $page;
    }

    /**
     * Insert billto & shipto blocks
     *
     * @param $page
     * @param $order
     */
    protected function drawAddresses($page, $order)
    {
        /* Add table head */
        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y -= 15;
        $page->drawText(__('Bill to :'), 30, $this->y, 'UTF-8');
        $page->drawText(__('Ship to :'), 300, $this->y, 'UTF-8');

        $billingAddress = explode("\n", $order->getBillingAddress());
        $shippingAddress = explode("\n", $order->getShippingAddress());

        $this->_setFontRegular($page, 12);
        $i = 0;
        foreach($billingAddress as $line) {
            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 60, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        $j = 0;
        foreach($shippingAddress as $line) {
            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 330, $this->y - 20 - ($j * 13), 'UTF-8');
                $j++;
            }
        }

        $maxLines = max(($i), ($j));

        $this->y -= $maxLines * 20 + 20;
    }

    protected function drawPoInformation($page, $order)
    {

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 105);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Purchase Order # %1', $order->getPoReference()), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $additionnalTxt = [];
        $additionnalTxt[] = __('Supplier : %1', $order->getSupplier()->getsup_name());
        $additionnalTxt[] = __('Manager : %1', $order->getManager()->getfirstname().' '.$order->getManager()->getlastname());
        $additionnalTxt[] = __('Estimated time of reception : %1', $order->getpo_eta());
        $additionnalTxt[] = __('Payment terms : %1', $order->getSupplier()->getsup_payment_terms());
        if ($order->getpo_supplier_reference())
            $additionnalTxt[] = __('Supplier order # : %1', $order->getpo_supplier_reference());
        $i = 0;
        foreach($additionnalTxt as $txt)
        {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 13), 'UTF-8');
            $i++;
        }

        $this->y -= 115;

    }

    protected function drawPublicComments($page, $order)
    {
        $comments = $order->getpo_public_comments();

        if (!$comments)
            return $this;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);

        $comments = explode("\r\n", $comments);
        $lineCount = count($comments) + 1;
        $page->drawRectangle(25, $this->y, 570, $this->y - 50 - ($lineCount * 13));

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 14);
        $page->drawText(__('Special instructions :'), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);

        foreach ($comments as $i => $line)
            $page->drawText($line, 60, ($this->y - 40 - ($i *13)), 'UTF-8');

        $this->y -= 60 + ($lineCount * 13);
    }

    public function insertAdditionnal($page, $order)
    {
        //nothing, used for drop ship
    }

    public function getPdfObject()
    {
        return $this->_getPdf();
    }

    public function setFontBold($page, $size)
    {
        $this->_setFontBold($page, $size);
        return $this;
    }

    public function setFontRegular($page, $size)
    {
        $this->_setFontRegular($page, $size);
        return $this;
    }

    protected function splitTextToSize($text, $font, $fontSize, $maxWidth)
    {
        $textSize = $this->widthForStringUsingFontSize($text, $font, $fontSize);
        $lines = [];

        if ($textSize > $maxWidth)
        {
            $words = explode(' ', $text);
            $currentLine = '';
            foreach($words as $word)
            {
                if ($this->widthForStringUsingFontSize($currentLine.$word, $font, $fontSize) < $maxWidth)
                    $currentLine .= $word.' ';
                else
                {
                    $lines[] = $currentLine;
                    $currentLine = $word.' ';
                }
            }

            if($currentLine != ''){
                $lines[] = $currentLine;
            }
        }
        else
            $lines[] = $text;

        return $lines;
    }
}
