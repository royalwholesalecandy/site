<?php namespace BoostMyShop\AdvancedStock\Model\StockTake;


class CsvExport {

    const SEPARATOR = ";";
    const LINE_RETURN = "\r\n";

    public function getCsvContent($stockTake)
    {
        $content = $this->getHeader().self::LINE_RETURN.$this->getLines($stockTake);

        return $content;
    }

    public function getFileName($stockTake)
    {
        $fileName = [];

        $fileName[] = 'Stock take';
        $fileName[] = $stockTake->getsta_name();
        $fileName[] = $stockTake->getWarehouseLabel();
        $fileName[] = date('Y-m-d H:i:s');

        return implode(' ', $fileName).'.csv';
    }

    public function getMimeType()
    {
        return 'text/csv';
    }

    public function getHeader()
    {
        $columns = [];

        $columns[] = "sku";
        $columns[] = "product";
        $columns[] = "qty_expected";
        $columns[] = "qty_scanned";
        $columns[] = "location";
        $columns[] = "status";

        return implode(self::SEPARATOR, $columns);
    }

    public function getLines($stockTake)
    {
        $lines = [];

        foreach($stockTake->getItems() as $item)
        {
            $columns = [];
            $columns[] = $item->getstai_sku();
            $columns[] = $item->getstai_name();
            $columns[] = $item->getstai_expected_qty();
            $columns[] = $item->getstai_scanned_qty();
            $columns[] = $item->getstai_location();
            $columns[] = $item->getstai_status();

            $lines[] = implode(self::SEPARATOR, $columns);
        }


        return implode(self::LINE_RETURN, $lines);
    }

}