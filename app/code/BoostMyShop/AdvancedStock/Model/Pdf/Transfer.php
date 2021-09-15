<?php namespace BoostMyShop\AdvancedStock\Model\Pdf;

/**
 * Class Transfer
 *
 * @package   BoostMyShop\AdvancedStock\Model\Pdf
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Transfer extends AbstractPdf {

    /**
     * Retrieve PDF
     *
     * @return \Zend_Pdf
     */
    public function getPdf($transfers = [])
    {

        $this->_beforeGetPdf();
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach($transfers as $transfer){

            $page = $this->newPage();
            $this->insertLogo($page, $this->getStoreIdFromTransfer($transfer));
            $this->_drawCompanyInformation($page, $transfer);
            $this->_drawTransferInformation($page, $transfer);
            $this->_drawHeader($page);

            foreach($transfer->getItems() as $item){
                $page = $this->_drawItem($item, $page, $transfer);
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
     * @param \Zend_Pdf_Page $page
     * @throws \Magento\Framework\Exception\LocalizedException
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
        $lines[0][] = ['text' => __('Product'), 'feed' => 90, 'align' => 'left'];
        $lines[0][] = ['text' => __(' '), 'feed' => 150, 'align' => 'left'];
        $lines[0][] = ['text' => __('Barcode'), 'feed' => 280, 'align' => 'left'];
        $lines[0][] = ['text' => __('Source location'), 'feed' => 430, 'align' => 'right'];
        $lines[0][] = ['text' => __('Target location'), 'feed' => 550, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;

    }

    /**
     * @param \Zend_Pdf_Page $page
     */
    protected function _drawCompanyInformation($page, $transfer){

        /* Add table head */
        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        $this->y -= 15;
        $page->drawText($this->_scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreIdFromTransfer($transfer)), 30, $this->y, 'UTF-8');

        $address = $this->_scopeConfig->getValue('sales/identity/address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreIdFromTransfer($transfer));
        $this->_setFontRegular($page, 12);
        $i = 0;
        foreach(explode("\n", $address) as $line){
            $line = str_replace("\r", "", $line);
            if ($line) {
                $page->drawText($line, 25, $this->y - 20 - ($i * 13), 'UTF-8');
                $i++;
            }
        }

        $this->y -= $i * 20 + 20;

    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param \BoostMyShop\AdvancedStock\Model\Transfer $transfer
     */
    protected function _drawTransferInformation($page, $transfer){

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 105);

        $this->_setFontBold($page, 14);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Stock Transfer # %1', $transfer->getst_reference()), 30, $this->y - 20, 'UTF-8');

        $this->_setFontRegular($page, 12);
        $additionnalTxt = [];
        $additionnalTxt[] = __('Status : %1', $transfer->getst_status());
        $additionnalTxt[] = __('Created at : %1', $transfer->getst_created_at());
        $additionnalTxt[] = __('From warehouse : %1', $transfer->getFromWarehouseName());
        $additionnalTxt[] = __('To warehouse : %1', $transfer->getToWarehouseName());
        $additionnalTxt[] = __('Notes : %1', $transfer->getst_notes());

        $i = 0;
        foreach($additionnalTxt as $txt)
        {
            $page->drawText($txt, 60, $this->y - 40 - ($i * 13), 'UTF-8');
            $i++;
        }

        $this->y -= 115;

    }

    /**
     * @param \BoostMyShop\AdvancedStock\Model\Transfer\Item $item
     * @param \Zend_Pdf_Page $page
     * @param \BoostMyShop\AdvancedStock\Model\Transfer $transfer
     * @return \Zend_Pdf_Page $page
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _drawItem($item, $page, $transfer){

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

        $product = $item->getsku()."\n".$item->getproduct_name();

        //columns headers
        $lines[0][] = ['text' => $item->getst_qty(), 'feed' => 45, 'align' => 'right'];
        $lines[0][] = ['text' => $item->getsku(), 'feed' => 90, 'align' => 'left'];
        $lines[0][] = ['text' => '', 'feed' => 150, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getBarcode(), 'feed' => 280, 'align' => 'left'];
        $lines[0][] = ['text' => $item->getTransferItem()->getFromShelfLocation($transfer->getst_from()), 'feed' => 430, 'align' => 'right'];
        $lines[0][] = ['text' => $item->getTransferItem()->getToShelfLocation($transfer->getst_to()), 'feed' => 550, 'align' => 'right'];

        $lines[1][] = ['text' => '', 'feed' => 45, 'align' => 'right'];
        $lines[1][] = ['text' => $item->getname(), 'feed' => 90, 'align' => 'left'];

        $lineBlock = ['lines' => $lines, 'height' => 13];

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page = $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;

        return $page;

    }

    protected function getStoreIdFromTransfer($transfer)
    {
        return $transfer->getWebsite()->getDefaultStore()->getId();
    }

}