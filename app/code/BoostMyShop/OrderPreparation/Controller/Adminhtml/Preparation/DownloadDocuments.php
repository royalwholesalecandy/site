<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;

class DownloadDocuments extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $userId = $this->_preparationRegistry->getCurrentOperatorId();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();
        $orders = $this->_objectManager->get('\BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory')
            ->create()
            ->addOrderDetails()
            ->addUserFilter($userId)
            ->addWarehouseFilter($warehouseId);

        if (count($orders) > 0)
        {
            $pdf = new \Zend_Pdf();

            foreach($orders as $order)
            {
                if ($this->_compatibilityHelper->hasFoomanPdfCustomizerInstalled())
                    $this->appendFoomanDocuments([$order], $pdf);
                else
                    $this->appendMagentoDocuments([$order], $pdf);
            }

            $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
            $fileName = 'documents_' . $date . '.pdf';
            $this->_objectManager->get('\Magento\Framework\App\Response\Http\FileFactory')->create(
                $fileName,
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );

            //delete file
            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $dir->delete($fileName);
        }
        else
        {
            $this->messageManager->addError(__('There is no order in progress.'));
            $this->_redirect('*/*/index');
        }

    }

    protected function appendMagentoDocuments($orders, &$pdf)
    {
        //append invoice
        if ($this->_configFactory->create()->includeInvoiceInDownloadDocuments()) {
            $invoiceIds = $this->getInvoiceIds($orders);
            if (count($invoiceIds) > 0) {
                $invoices = $this->_invoiceCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('entity_id', $invoiceIds);
                $invoicePdf = $this->_invoicePdf->getPdf($invoices);
                foreach ($invoicePdf->pages as $page)
                    $pdf->pages[] = $page;
            }
        }

        //append packing slip
        if ($this->_configFactory->create()->includeShipmentInDownloadDocuments()) {
            $shipmentIds = $this->getShipmentIds($orders);
            if (count($shipmentIds) > 0) {
                $shipments = $this->_shipmentCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('entity_id', $shipmentIds);
                $shipmentPdf = $this->_shipmentPdf->getPdf($shipments);
                foreach ($shipmentPdf->pages as $page)
                    $pdf->pages[] = $page;
            }
        }
    }

    protected function appendFoomanDocuments($orders, &$pdf)
    {
        $extractor = new \Zend_Pdf_Resource_Extractor();

        //append invoice
        if ($this->_configFactory->create()->includeInvoiceInDownloadDocuments()) {
            $invoiceIds = $this->getInvoiceIds($orders);
            if (count($invoiceIds) > 0) {
                $invoices = $this->_invoiceCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('entity_id', $invoiceIds);
                $foomanObject = $this->getObjectManager()->create('\Fooman\PdfCustomiser\Model\PdfRenderer\InvoiceAdapter');
                $t = [];
                foreach($invoices as $invoice)
                    $t[] = $invoice;
                $pdfString = $foomanObject->getPdfAsString($t);
                $tcpdf = \Zend_Pdf::parse($pdfString);
                foreach ($tcpdf->pages as $p) {
                    $pdf->pages[] = $extractor->clonePage($p);
                }
            }
        }

        //append packing slip
        if ($this->_configFactory->create()->includeShipmentInDownloadDocuments()) {
            $shipmentIds = $this->getShipmentIds($orders);
            if (count($shipmentIds) > 0) {
                $shipments = $this->_shipmentCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('entity_id', $shipmentIds);
                $foomanObject = $this->getObjectManager()->create('\Fooman\PdfCustomiser\Model\PdfRenderer\ShipmentAdapter');
                $t = [];
                foreach($shipments as $shipment)
                    $t[] = $shipment;
                $pdfString = $foomanObject->getPdfAsString($t);
                $tcpdf = \Zend_Pdf::parse($pdfString);
                foreach ($tcpdf->pages as $p) {
                    $pdf->pages[] = $extractor->clonePage($p);
                }
            }
        }

    }

    protected function getInvoiceIds($orders)
    {
        $ids = [];

        foreach($orders as $order)
        {
            if ($order->getip_invoice_id())
                $ids[] = $order->getip_invoice_id();
        }

        return $ids;
    }

    protected function getShipmentIds($orders)
    {
        $ids = [];

        foreach($orders as $order)
        {
            if ($order->getip_shipment_id())
                $ids[] = $order->getip_shipment_id();
        }

        return $ids;
    }

    protected function getObjectManager()
    {
        if (null == $this->_objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->_objectManager = $this->_objectManagerFactory->create($_SERVER);
            /** @var \Magento\Framework\App\State $appState */
            $appState = $this->_objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode($area);
            $configLoader = $this->_objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
            $this->_objectManager->configure($configLoader->load($area));
        }
        return $this->_objectManager;
    }

}
