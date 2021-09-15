<?php namespace BoostMyShop\AdvancedStock\Model\Pdf;

/**
 * Class StockTake
 *
 * @package   BoostMyShop\AdvancedStock\Model\Pdf
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StockTake extends AbstractPdf {


    /**
     * Retrieve PDF
     *
     * @param array $stockTakes
     * @return \Zend_Pdf
     */
    public function getPdf($stockTakes = [])
    {
        $this->_beforeGetPdf();
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        $orderBy = [
            'stai_sku' => 'desc',
            'stai_status' => 'desc'
        ];

        foreach($stockTakes as $stockTake){

            $page = $this->newPage();
            $this->insertLogo($page, null);
            $this->_drawInformation($page, $stockTake);
            $this->_drawHeader($page);

            foreach($stockTake->getItems(['order' => $orderBy]) as $item){

                //check available space
                if ($this->y < 50) {
                    $page = $this->newPage();
                    $this->_drawHeader($page);
                }

                $page = $this->_drawItem($item, $page, $stockTake);

            }

        }


        return $pdf;
    }

    /**
     * @param array $settings
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @param $stockTake
     * @return \Zend_Pdf_Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _drawItem($item, $page, $stockTake)
    {

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        //columns headers
        $lines[0][] = ['text' => $item->getstai_expected_qty(), 'feed' => 30, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getstai_scanned_qty(), 'feed' => 90, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getstai_manufacturer(), 'feed' => 360, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getstai_location(), 'feed' => 480, 'align' => 'right'];
        $lines[0][] = ['text' => $item->getStatusLabel(), 'feed' => 550, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y += 5;
        //1) DISPLAY DATA
        //top y baseline
        $baseDisplayY = $this->y;
        $interlineHeight = 10;

        $wordLimitNumber = 13;

        $skuCleaned = preg_replace("#[^a-zA-Z]#", "", $item->getstai_sku());
        if (ctype_upper($skuCleaned)) {
            $wordLimitNumber = 12;
        }

        $skuWrapped = wordwrap($item->getstai_sku(), $wordLimitNumber, ' ', true);
        if (strlen($skuWrapped) == 15) {
            if(substr($skuWrapped, -2, 1) == ' '){
                $skuWrapped = substr_replace($skuWrapped, '', -2, 1);
            }
        }


        $skuLines = $this->splitTextForLineWeight($skuWrapped, 210, $page->getFont(), 25);
        //DISPLAY PRODUCT SKU
        foreach($skuLines as $skuLine) {
            $page->drawText($skuLine, 120, $this->y, 'UTF-8');
            $this->y -= $interlineHeight;
        }

        $endDisplayYAfterSku = $this->y;
        //SET GAIN Y BASELINE TO VERTICAL ALIGN
        $this->y = $baseDisplayY;
        $nameLines = $this->splitTextForLineWeight($item->getstai_name(), 450, $page->getFont(),30);
        //DISPLAY PRODUCT NAME
        foreach($nameLines as $nameLine) {
            $page->drawText($nameLine, 202, $this->y, 'UTF-8');
            $this->y -= $interlineHeight;
        }
        $endDisplayYAfterName = $this->y;
        //keep the lowest Y to avoid text override
        $this->y = ($endDisplayYAfterSku<$endDisplayYAfterName)?$endDisplayYAfterSku:$endDisplayYAfterName;
        //bottom item margin
        $this->y -= $interlineHeight;

        return $page;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param \BoostMyShop\AdvancedStock\Model\StockTake $stockTake
     * @return \Zend_Pdf_Page
     */
    protected function _drawInformation($page, $stockTake){

        /* Add table head */
        $this->_setFontRegular($page, 12);
        $i = 0;

        $info = array();
        $info[] = __('Reference').' : '.$stockTake->getsta_name();
        $info[] = __('Warehouse').' : '.$stockTake->getWarehouseLabel();
        $info[] = __('Created at').' : '.$stockTake->getsta_created_at();
        $info[] = __('Progress').' : '.$stockTake->getsta_progress();
        $productSelectionMode = __('Mode').' : '.$stockTake->getProductSelectionLabel();

        $info[] = $productSelectionMode;
        $info[] = __('Notes').' : ';

        foreach($info as $line){
            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 25, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        foreach(explode("\n", $stockTake->getsta_notes()) as $line) {

            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 35, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }

        }

        $this->y -= $i * 20;

        return $page;

    }

    /**
     * @param \Zend_Pdf_Page $page
     * @return \Zend_Pdf_Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _drawHeader(\Zend_Pdf_Page $page){

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;

        //columns headers

        $lines[0][] = ['text' => __('Qty Expected'), 'feed' => 30, 'align' => 'left'];
        $lines[0][] = ['text' => __('Qty Scanned'), 'feed' => 90, 'align' => 'left'];
        $lines[0][] = ['text' => __('Sku'), 'feed' => 180, 'align' => 'right'];
        $lines[0][] = ['text' => __('Name'), 'feed' => 205, 'align' => 'left'];
        $lines[0][] = ['text' => __('Manufacturer'), 'feed' => 415, 'align' => 'right'];
        $lines[0][] = ['text' => __('Location'), 'feed' => 480, 'align' => 'right'];
        $lines[0][] = ['text' => __('Status'), 'feed' => 550, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;

        return $page;

    }

    public function splitTextForLineWeight($txt, $maxWidth, $font, $fontSize)
    {
        $lines = [];
        $words = explode(' ', $txt);
        $currentLine = '';
        foreach($words as $word)
        {
            if ($this->widthForStringUsingFontSize($currentLine.' '.$word, $font, $fontSize) > $maxWidth)
            {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
            else
                $currentLine .= ' '.$word;
        }
        $lines[] = $currentLine;
        return $lines;
    }
}