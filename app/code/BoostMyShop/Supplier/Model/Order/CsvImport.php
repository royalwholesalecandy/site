<?php

namespace BoostMyShop\Supplier\Model\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class CsvImport
{

    protected $_orderProductFactory;
    protected $_httpFactory;
    protected $_dir;
    protected $_uploaderFactory;
    protected $_importHandler;

    public function __construct(
        Filesystem $filesystem,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductFactory,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \BoostMyShop\Supplier\Model\Order\ProductsImportHandler $importHandler,
        \Magento\Framework\File\UploaderFactory $uploaderFactory
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_orderProductFactory = $orderProductFactory;
        $this->_httpFactory = $httpFactory;
        $this->_dir = $dir;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_importHandler = $importHandler;
    }

    public function getCsvFile($headers,$order)
    {
        $orderdetail = array();
        $name = strtotime("now");
        $file = 'export/orderexport' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        
        /* Herder for Supplier, Purchase order # and ETA */
        $supplierHeader = array('Supplier', $order->getSupplier()->getSupName());
        $poHeader = array('Purchase order #',$order->getData('po_reference'));
        $etaHeader = array('ETA',$order->getData('po_eta'));

        $stream->writeCsv($supplierHeader);
        $stream->writeCsv($poHeader);
        $stream->writeCsv($etaHeader);

        /*Header for PO products */
        $stream->writeCsv($headers);
        $collection = $this->_orderProductFactory->create();
        $collection->addOrderFilter($order->getId());
        
        foreach($collection as $product){
            $orderdetail['sku'] = $product->getData('pop_sku');
            $orderdetail['supplier_sku'] = $product->getData('pop_supplier_sku');
            $orderdetail['name'] = $product->getData('pop_name');
            $orderdetail['qty'] = $product->getData('pop_qty');
            $orderdetail['buying_price'] = $product->getData('pop_price');
            $orderdetail['tax_rate'] = $product->getData('pop_tax_rate');
            
            $stream->writeCsv($orderdetail);
        }

        $stream->unlock();
        $stream->close();
        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * @param \BoostMyShop\Supplier\Model\Order $order
     * @param int $poId
     * @return void
     */
    public function checkPoImport($order, $poId, $delimiter)
    {
        try
        {
            $adapter = $this->_httpFactory->create();
            if ($adapter->isValid('import_file')) {
                $destinationFolder = $this->_dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $uploader = $this->_uploaderFactory->create(array('fileId' => 'import_file'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setAllowedExtensions(['csv']);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                $fullPath = $result['path'].$result['file'];

                return $this->_importHandler->importFromCsvFile($poId, $order, $fullPath, $delimiter);
            }

        }
        catch(\Exception $ex)
        {
            throw new \Exception($ex->getMessage());
        }

    }

}
