<?php

namespace BoostMyShop\Supplier\Model\Supplier;


class ImportHandler
{

    protected $csvProcessor;

    protected $fieldsIndexes = [];

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_supplierFactory = $supplierFactory;
    }

    public function importFromCsvFile($path, $delimiter = ';')
    {

        //perform checks
        $this->csvProcessor->setDelimiter($delimiter);
        $rows = $this->csvProcessor->getData($path);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        $count = 0;
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            if ($this->_importRow($rowData))
                $count++;
        }

        return $count;
    }



    protected function _importRow($rowData)
    {
        $code = '';
        if (isset($this->fieldsIndexes['sup_code']))
            $code = $rowData[$this->fieldsIndexes['sup_code']];
        if (!$code)
            return false;

        $supplier = $this->_supplierFactory->create()->load($code, 'sup_code');
        foreach($this->fieldsIndexes as $k => $index)
        {
            $supplier->setData($k, $rowData[$this->fieldsIndexes[$k]]);
        }
        $supplier->save();
        return true;
    }

    public function checkColumns($columns)
    {
        $mandatory = [
            0 => 'sup_code'
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

}
