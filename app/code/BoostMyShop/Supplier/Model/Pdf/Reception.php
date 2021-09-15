<?php

namespace BoostMyShop\Supplier\Model\Pdf;


class Reception extends AbstractPdf
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

    protected $_skuFeed = 70;

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

            //columns headers
        $lines[0][] = ['text' => __('Qty'), 'feed' => 45, 'align' => 'right'];
        $lines[0][] = ['text' => __('Sku'), 'feed' => 60, 'align' => 'left'];
        $lines[0][] = ['text' => __('Supplier Sku'), 'feed' => 200, 'align' => 'left'];
        $lines[0][] = ['text' => __('Product'), 'feed' => 300, 'align' => 'left'];
        $lines[0][] = ['text' => __('Location'), 'feed' => 500, 'align' => 'left'];

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
    public function getPdf($receptions = [])
    {
        $this->_beforeGetPdf();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($receptions as $reception) {

            $page = $this->newPage();

            /* Add image */
            $this->insertLogo($page, 0);

            /* Add document text and number */
            $this->drawReceptionInformation($page, $reception);

            $this->_drawHeader($page);

            $items = $this->createItemsArray($reception);

            /* Add body */
            foreach ($items as $item) {

                $this->_drawItem($item, $page, $reception);

                $page = end($pdf->pages);
            }

        }
        $this->_afterGetPdf();
        return $pdf;
    }

    protected function createItemsArray($reception)
    {
        $items = [];

        foreach ($reception->getAllItems() as $item) {
            $items[] = ['item' => $item, 'location' => $this->_product->getLocation($item->getpop_product_id(), $reception->getOrder()->getpo_warehouse_id())];
        }

        uasort($items, array('\BoostMyShop\Supplier\Model\Pdf\Reception', 'sortProductsPerLocation'));

        return $items;
    }

    public static function sortProductsPerLocation($a, $b)
    {
        return ($a['location'] > $b['location']);
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
    protected function _drawItem($item, $page, $reception)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $lines[0][] = ['text' => $item['item']->getTotalQty(), 'feed' => 40, 'align' => 'right'];
        $lines[0][] = ['text' => $item['item']->getpop_sku(), 'feed' => 60, 'align' => 'left'];
        $lines[0][] = ['text' => $item['item']->getpop_supplier_sku(), 'feed' => 200, 'align' => 'left'];
        $lines[0][] = ['text' => $item['location'], 'feed' => 530, 'align' => 'left'];
        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y += 5;

        $barcode = $this->_product->getBarcode($item['item']->getpop_product_id());
        if ($barcode)
            $page->drawText($barcode, 60, $this->y - 11, 'UTF-8');

        $nameLines = $this->splitTextToSize($item['item']->getPopName(), $page->getFont(), 10, 180);
        foreach($nameLines as $nameLine) {
            $page->drawText($nameLine, 300, $this->y, 'UTF-8');
            $this->y -= 10;
        }

        $this->y -= 20;
    }

    protected function drawReceptionInformation($page, $reception)
    {

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 105);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Reception # %1', $reception->getpor_id()), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $additionnalTxt = [];
        $additionnalTxt[] = __('Supplier: %1', $reception->getOrder()->getSupplier()->getsup_name());
        $additionnalTxt[] = __('Purchase order: %1', $reception->getOrder()->getpo_reference());
        $additionnalTxt[] = __('Reception date: %1', $reception->getpor_created_at());
        $additionnalTxt[] = __('Received by: %1', $reception->getpor_username());
        $additionnalTxt[] = __('Products count: %1', $reception->getpor_product_count());

        $i = 0;
        foreach($additionnalTxt as $txt)
        {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 13), 'UTF-8');
            $i++;
        }

        $this->y -= 115;

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
