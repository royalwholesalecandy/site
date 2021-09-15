<?php namespace BoostMyShop\OrderPreparation\Model\Pdf;

class PickingList extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    protected $_storeManager;
    protected $_messageManager;
    protected $_localeResolver;
    protected $_config;
    protected $_product;
    protected $_barcode;
    protected $_orderItemFactory;
    protected $_displaySummary;
    protected $_preparationRegistry = true;
    protected $_eventManager;

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
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \BoostMyShop\OrderPreparation\Model\Config $config,
        \BoostMyShop\OrderPreparation\Model\ProductFactory $product,
        \BoostMyShop\OrderPreparation\Model\Pdf\Barcode $barcode,
        \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->_messageManager = $messageManager;
        $this->_localeResolver = $localeResolver;
        $this->_config = $config;
        $this->_product = $product;
        $this->_barcode = $barcode;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_displaySummary = $this->_config->includeGlobalPickingList();

        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
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
        if ($this->_config->getPdfPickingLayout() != 'small') {
            $this->y -= 20;
            return;
        }

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;

        //columns headers
        $lines[0][] = ['text' => __('Qty'), 'feed' => 100, 'align' => 'center'];
        $lines[0][] = ['text' => __('Location'), 'feed' => 130, 'align' => 'left'];
        $lines[0][] = ['text' => __('SKU'), 'feed' => 200, 'align' => 'left'];
        $lines[0][] = ['text' => __('Product'), 'feed' => 350, 'align' => 'left'];

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

        if (!$this->_displaySummary && !$this->_config->pickingListOnePagePerOrder()) {
            throw new \Exception(
                'The PDF is empty because both "Include single order picklist" and "Include global picklist" options are set to "No" in the configuration.'
            );
        }

        if ($this->_displaySummary) {
            $this->addSummaryPage($orders);
        }

        if ($this->_config->pickingListOnePagePerOrder()) {
            foreach ($orders as $orderInProgress) {
                $tItems = [];
                foreach ($orderInProgress->getAllItems() as $item) {

                    //Exclude downloadable products
                    if ($item->getproduct_type() == "downloadable") {
                        continue;
                    }

                    $item->setproductType($item->getproduct_type());
                    $item->setLocation($this->_product->create()->getLocation($item->getproduct_id(), $this->_preparationRegistry->getCurrentWarehouseId()));
                    $item->setBarcode($this->_product->create()->getBarcode($item->getproduct_id()));
                    $item->setOptions($this->getOptionsAsText($item));
                    $item->setConfigurableOptions($this->getConfigurableOptionsAsText($item));
                    $item->setParentName($this->getParentName($item));
                    $tItems[] = $item;
                }

                //If no products to draw for current order, skip order page
                if (count($tItems) <= 0) {
                    continue;
                }

                usort($tItems, function ($a, $b) {
                    return strcmp($a->getParentName().$a->getLocation(), $b->getParentName().$b->getLocation());
                });

                $page = $this->newPage();
                $this->insertLogo($page, $orderInProgress->getStore());
                $this->insertBarcode($page, $orderInProgress->getOrder()->getincrement_id());
                $this->drawOrderInformation($page, $orderInProgress);
                $this->drawAddresses($page, $orderInProgress->getOrder());

                $this->_eventManager->dispatch(
                    'bms_orderpreparation_picking_list_before_print_products',
                    [
                        'page' => $page,
                        'pickinglist' => $this,
                        'orderId' => $orderInProgress->getOrder()->getId()
                    ]
                );

                /* Add body */
                $this->_drawHeader($page);

                foreach ($tItems as $item) {
                    $this->_drawProduct($item, $page, true);
                    if ($this->y < 50) {
                        $page = $this->newPage();
                        $this->drawOrderInformation($page, $orderInProgress);
                        $this->y -= 30;
                    }
                    $page = end($pdf->pages);
                }
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    protected function insertLogo(&$page, $store = null)
    {
        $previousY = $this->y;
        parent::insertLogo($page, $store);
        if ($previousY == $this->y) {
            $this->y -= 50;
        }
    }

    /**
     * @param $orders
     */
    protected function addSummaryPage($orders)
    {
        //get items summary
        $storeId = false;
        $items = [];
        foreach ($orders as $orderInProgress) {
            //initialize store id with first order
            if (!$storeId) {
                $storeId = $orderInProgress->getOrder()->getStoreId();
            }

            foreach ($orderInProgress->getAllItems() as $item) {

                //Exclude downloadable products from items to draw
                if ($item->getproduct_type() == "downloadable") {
                    continue;
                }

                $key = $item->getproduct_id();
                if ($this->_config->displayCustomOptionsOnPicking()) {
                    $key .= '_'.$this->getOptionsKey($item);
                }

                if (!isset($items[$key])) {
                    $obj = new \Magento\Framework\DataObject();
                    $obj->setproduct_id($item->getproduct_id());
                    $obj->setsku($item->getsku());
                    $obj->setproductType($item->getproduct_type());
                    $obj->setBarcode($this->_product->create()->getBarcode($item->getproduct_id()));
                    $obj->setname($item->getname());
                    $obj->setLocation($this->_product->create()->getLocation($item->getproduct_id(), $this->_preparationRegistry->getCurrentWarehouseId()));
                    $obj->setorders([]);
                    $obj->setOptions($this->getOptionsAsText($item));
                    $obj->setConfigurableOptions($this->getConfigurableOptionsAsText($item));
                    $items[$key] = $obj;
                }

                $items[$key]->setipi_qty($items[$key]->getipi_qty() + $item->getipi_qty());
            }
        }

        //If no products to draw in summary page (and so in all the picking list PDF), throw error
        if (count($items) <= 0) {
            throw new \Exception(
                'The PDF is empty because there are no products to display (downloadable products are not displayed).'
            );
        }

        usort($items, function ($a, $b) {
            return strcmp($a->getLocation(), $b->getLocation());
        });

        $page = $this->newPage();
        $this->insertLogo($page, $storeId);

        $this->_setFontBold($page, 18);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 15;
        $page->drawText(__('Global Picking Sheet'), 30, $this->y, 'UTF-8');
        $this->y -= 40;

        $this->_drawHeader($page);
        foreach ($items as $item) {
            $this->_drawProduct($item, $page, false);
            if ($this->y < 50) {
                $page = $this->newPage();
            }
        }
    }

    /**
     * @param $page
     * @param $barcodeNumber
     */
    protected function insertBarcode($page, $barcodeNumber)
    {
        $barcodeImage = $this->_barcode->getZendPdfBarcodeImage($barcodeNumber);
        $x = 420;
        $y = 820;
        $width = 160;
        $height = 50;
        $page->drawImage($barcodeImage, $x, $y - $height, $x + $width, $y);
        return $this;
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
    protected function _drawProduct($item, $page, $drawParent = false)
    {
        $separator = '';
        if ($item->getBarcode()) {
            $separator = ' / ';
        }

        switch ($this->_config->getPdfPickingLayout()) {
            case 'small':

                if ($drawParent && $item->getParentName()) {
                    $this->y -= 3;
                    $page->drawText($item->getParentName(), 100, $this->y, 'UTF-8');
                    $this->y -= 5;
                    $page->drawLine(100, $this->y, 470, $this->y);
                    $this->y -= 13;
                }

                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

                //columns headers
                $lines[0][] = ['text' => $item->getipi_qty(), 'feed' => 100, 'align' => 'center'];
                $lines[0][] = ['text' => $item->getLocation(), 'feed' => 130, 'align' => 'left'];
                $lines[0][] = ['text' => $item->getSku().$separator.$item->getBarcode(), 'feed' => 200, 'align' => 'left'];
                //$lines[0][] = ['text' => $item->getName(), 'feed' => 350, 'align' => 'left'];
                $lineBlock = ['lines' => $lines, 'height' => 5];

                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

                $this->y += 15;
                $nameLines = $this->splitTextToSize($item->getName(), $page->getFont(), 12, 200);
                if ($this->_config->displayCustomOptionsOnPicking() && $item->getOptions()) {
                    foreach ($item->getOptions() as $option) {
                        $nameLines[] = $option;
                    }
                }

                if ($item->getConfigurableOptions()) {
                    foreach ($item->getConfigurableOptions() as $option) {
                        $nameLines[] = $option;
                    }
                }

                foreach ($nameLines as $nameLine) {
                    $page->drawText($nameLine, 350, $this->y - 15 + 5, 'UTF-8');
                    $this->y -= 10;
                }

                $this->y -= 20;

                break;
            case 'large':
                $imagePath = $this->_product->create()->getImagePath($item->getproduct_id());
                if ($this->_mediaDirectory->isFile($imagePath)) {
                    try {
                        $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                        $page->drawImage($image, 50, $this->y - 15, 50 + 40, $this->y - 15 + 40);
                    } catch (\Exception $ex) {
                        //nothing
                    }
                }

                $this->_setFontRegular($page, 24);
                $page->drawText($item->getipi_qty().'x', 120, $this->y, 'UTF-8');

                $this->_setFontRegular($page, 18);
                $page->drawText($item->getLocation(), 200, $this->y, 'UTF-8');

                $this->_setFontRegular($page, 12);
                $page->drawText($item->getSku().$separator.$item->getBarcode(), 300, $this->y + 5, 'UTF-8');

                $finalOffset = 50;

                $nameLines = $this->splitTextToSize($item->getName(), $page->getFont(), 12, 200);
                foreach ($nameLines as $nameLine) {
                    $page->drawText($nameLine, 300, $this->y - 15 + 5, 'UTF-8');
                    $this->y -= 20;
                    $finalOffset -= 20;
                }

                if ($this->_config->displayCustomOptionsOnPicking() && $item->getOptions()) {
                    foreach ($item->getOptions() as $option) {
                        $page->drawText($option, 300, $this->y - 5, 'UTF-8');
                        $this->y -= 20;
                        $finalOffset -= 5;
                    }
                }

                if ($item->getConfigurableOptions()) {
                    foreach ($item->getConfigurableOptions() as $option) {
                        $page->drawText($option, 300, $this->y - 5, 'UTF-8');
                        $this->y -= 20;
                        $finalOffset -= 5;
                    }
                }


                $this->y -= $finalOffset;

                $page->drawLine(25, $this->y + 26, 570, $this->y + 26);

                break;
        }
    }

    public function getConfigurableOptionsAsText($item)
    {
        $txt = array();

        if ($item->getOrderItem()->getparent_item_id()) {
            $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
            $options = $parentItem->getProductOptions();
            if (isset($options['attributes_info']) && is_array($options['attributes_info'])) {
                foreach ($options['attributes_info'] as $info) {
                    $txt[] = $info['label'].': '.$info['value'];
                }
            }
        }

        return $txt;
    }

    protected function getOptionsAsText($item)
    {
        $txt = [];
        $options = $item->getOrderItem()->getProductOptions();

        if (isset($options['options']) && count($options['options']) > 0) {
            foreach ($options['options'] as $option) {
                $txt[] = $option['label'].' : '.$option['print_value'];
            }
        } else {
            //try with parent
            if ($item->getOrderItem()->getparent_item_id()) {
                $parentItem = $this->_orderItemFactory->create()->load($item->getOrderItem()->getparent_item_id());
                $options = $parentItem->getProductOptions();
                if (isset($options['options']) && count($options['options']) > 0) {
                    foreach ($options['options'] as $option) {
                        $txt[] = $option['label'].' : '.$option['print_value'];
                    }
                }
            } else {
                return false;
            }
        }


        return $txt;
    }

    protected function getOptionsKey($item)
    {
        $txt = [];
        $options = $item->getOrderItem()->getProductOptions();

        if (isset($options['options']) && count($options['options']) > 0) {
            foreach ($options['options'] as $option) {
                $txt[] = $option['option_id'].' : '.$option['option_value'];
            }
        }

        return implode('_', $txt);
    }

    /**
     * @param $page
     * @param $order
     */
    protected function insertCosts($page, $order)
    {
        $costs = ['Shipping' => $order->getpo_shipping_cost(), 'Additionnal' => $order->getpo_additionnal_cost()];
        foreach ($costs as $label => $value) {
            if ($value > 0) {
                $lines = [];
                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
                $lines[0][] = ['text' => $label, 'feed' => 200, 'align' => 'left'];
                $lines[0][] = ['text' => $order->getCurrency()->format($value, [], false), 'feed' => 480, 'align' => 'right'];
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
        $totals[] = ['label' => __('Shipping & additionnal'), 'value' => $order->getPoShippingCost() + $order->getPoAdditionnalCost()];
        $totals[] = ['label' => __('Taxes'), 'value' => $order->getPoTax()];
        $totals[] = ['label' => __('Grand total'), 'value' => $order->getPoGrandtotal()];

        $page->drawLine(25, $this->y, 570, $this->y);
        $this->y -= 20;

        $this->_setFontBold($page, 18);

        foreach ($totals as $total) {
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
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 140);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y -= 15;
        $page->drawText(__('Bill to :'), 30, $this->y, 'UTF-8');
        $page->drawText(__('Ship to :'), 300, $this->y, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $billingAddress = $this->addressRenderer->format($order->getBillingAddress(), 'html');
        $billingAddress = str_replace("\n", "", $billingAddress);
        $billingAddress = str_replace("<br />", "<br/>", $billingAddress);

        $i = 0;
        foreach (explode("<br/>", $billingAddress) as $line) {
            $line = str_replace(chr(13), "", $line);
            $line = strip_tags($line);
            if ($line) {
                $page->drawText($line, 60, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        if ($order->getShippingAddress()) {
            $shippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
            $shippingAddress = str_replace("\n", "", $shippingAddress);
            $shippingAddress = str_replace("<br />", "<br/>", $shippingAddress);
            $i = 0;
            foreach (explode("<br/>", $shippingAddress) as $line) {
                $line = str_replace(chr(13), "", $line);
                $line = strip_tags($line);
                if ($line) {
                    $page->drawText($line, 330, $this->y - 20 - ($i * 13), 'UTF-8');
                    $i++;
                }
            }
        }

        $this->y -= 140;
    }

    protected function drawOrderInformation($page, $orderInProgress)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 90);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Order # %1', $orderInProgress->getOrder()->getIncrementId()), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $additionnalTxt = [];
        $additionnalTxt[] = __('Operator: %1', $orderInProgress->getOperatorName());
        $additionnalTxt[] = __('Shipping method: %1', $orderInProgress->getOrder()->getShippingDescription());
        $additionnalTxt[] = __('Date: %1', date('Y-m-d H:i:s'));
        $i = 0;
        foreach ($additionnalTxt as $txt) {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 13), 'UTF-8');
            $i++;
        }

        $this->y -= 100;
    }

    protected function drawPublicComments($page, $order)
    {
        $comments = $order->getpo_public_comments();

        if (!$comments) {
            return $this;
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 50);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 14);
        $page->drawText(__('Special instructions :'), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $page->drawText($comments, 60, $this->y - 40, 'UTF-8');

        $this->y -= 60;
    }

    public function splitTextToSize($text, $font, $fontSize, $maxWidth)
    {
        $textSize = $this->widthForStringUsingFontSize($text, $font, $fontSize);
        $lines = [];
        if ($textSize > $maxWidth) {
            $words = explode(' ', $text);
            $currentLine = '';
            foreach ($words as $word) {
                if ($this->widthForStringUsingFontSize($currentLine.$word, $font, $fontSize) < $maxWidth) {
                    $currentLine .= $word.' ';
                } else {
                    $lines[] = $currentLine;
                    $currentLine = $word.' ';
                }
            }
            
            if ($currentLine != '') {
                $lines[] = $currentLine;
            }
        } else {
            $lines[] = $text;
        }

        return $lines;
    }

    public function displaySummary($value)
    {
        $this->_displaySummary = $value;
        return $this;
    }

    public static function sortPerLocation($a, $b)
    {
        if ($a->getLocation() > $b->getLocation()) {
            return $a;
        } else {
            return $b;
        }
    }

    /**
     * Return parent name if parent is a bundle
     *
     * @param $item
     */
    public function getParentName($item)
    {
        if ($this->_config->getGroupBundleItems()) {
            if ($item->getParentItem() && $item->getParentItem()->getproduct_type() == 'bundle') {
                return $item->getParentItem()->getName();
            }
        }
    }
}
