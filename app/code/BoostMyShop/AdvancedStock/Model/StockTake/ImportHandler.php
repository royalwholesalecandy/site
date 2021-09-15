<?php namespace BoostMyShop\AdvancedStock\Model\StockTake;


class ImportHandler {

    protected $csvProcessor;
    protected $_product;
    protected $fieldsIndexes = [];
    protected $_warehouseId;
    protected $_warehouseItemFactory;
    protected $_stockMovementFactory;
    protected $_backendAuthSession;

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor
    ) {
        $this->csvProcessor = $csvProcessor;
    }

    public function importFromCsvFile($stockTake, $filePath)
    {
        if (!($filePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        //perform checks
        $this->csvProcessor->setDelimiter(';');
        $rows = $this->csvProcessor->getData($filePath);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        $stockTakeItems = $stockTake->getItems();
        $count = 0;
        $errors = [];
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            try
            {
                $this->_importRow($rowData, $stockTakeItems);
                $count++;
            }
            catch(\Exception $ex)
            {
                $errors[] = $ex->getMessage();
            }

        }

        return ['success' => $count, 'errors' => $errors];
    }

    public function checkColumns($columns)
    {
        $mandatory = [
            0 => 'sku',
            1 => 'qty_scanned'
        ];
        for($i=0;$i<count($columns);$i++)
        {
            $this->fieldsIndexes[$columns[$i]] = $i;
        }

        foreach($mandatory as $field)
        {
            if (!isset($this->fieldsIndexes[$field]))
                throw new \Exception('Mandatory column '.$field.' is missing');
        }

        return true;
    }

    protected function _importRow($rowData, $stockTakeItems)
    {
        $sku = $rowData[$this->fieldsIndexes['sku']];
        $qtyScanned = $rowData[$this->fieldsIndexes['qty_scanned']];

        //todo: improve this code, too much time consuming for large catalogs
        foreach($stockTakeItems as $item)
        {
            if ($item->getstai_sku() == $sku) {
                $item->setstai_scanned_qty($qtyScanned)->save();
                return;
            }
        }

        throw new \Exception('Unable to find product with sku '.$sku);
    }

}
