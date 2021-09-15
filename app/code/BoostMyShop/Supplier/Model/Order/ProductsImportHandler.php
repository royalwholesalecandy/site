<?php

namespace BoostMyShop\Supplier\Model\Order;


class ProductsImportHandler
{

    protected $csvProcessor;
    protected $_product;
    protected $fieldsIndexes = [];
    protected $_poId;
    protected $_order;
    protected $_orderProductCollectionFactory;
    protected $_orderProductFactory;

    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\Product $product,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCollectionFactory,
        \BoostMyShop\Supplier\Model\Order\ProductFactory $orderProductFactory
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->_product = $product;
        $this->_orderProductCollectionFactory = $orderProductCollectionFactory;
        $this->_orderProductFactory = $orderProductFactory;
    }

    public function importFromCsvFile($poId,$order,$filePath, $delimiter = ';')
    {
        $this->_poId = $poId;
        $this->_order = $order;

        if (!($filePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        //perform checks
        $this->csvProcessor->setDelimiter($delimiter);
        $rows = $this->csvProcessor->getData($filePath);
        if (!isset($rows[0]))
            throw new \Exception('The file is empty');
        $columns = $rows[0];
        $this->checkColumns($columns);

        //import rows
        $rowResult = array();
        foreach ($rows as $rowIndex => $rowData) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            $rowResult[] = $this->_importRow($rowData);
        }

        return $rowResult;
    }



    protected function _importRow($rowData)
    {
        $result = array();
        $sku = $rowData[$this->fieldsIndexes['sku']];
        $qty = $rowData[$this->fieldsIndexes['qty']];
        
        $buyingPrice = (isset($this->fieldsIndexes['buying_price']) ? $rowData[$this->fieldsIndexes['buying_price']] : '');
        $taxRate = (isset($this->fieldsIndexes['tax_rate']) ? $rowData[$this->fieldsIndexes['tax_rate']] : '');
        $supplierSku = (isset($this->fieldsIndexes['supplier_sku']) ? $rowData[$this->fieldsIndexes['supplier_sku']] : '');

        if($sku != '' && $qty != '')
        {
            //check sku
            $productId = $this->_product->getIdBySku($sku);

            if (!$productId)
            {
                $result['not_found'] = $sku;
            } 
            else 
            {
                $orderProduct = $this->_orderProductCollectionFactory->create()
                                    ->addOrderFilter($this->_poId)
                                    ->addProductFilter($productId)
                                    ->getFirstItem();

                /* if product is already added in PO then update qty, tax rate, price and supplier sku
                 * else add product in PO
                */
                if($orderProduct->getId())
                {
                    $orderProduct->setpop_qty($qty);
                    if ($taxRate != '')
                        $orderProduct->setpop_tax_rate($taxRate);

                    if ($buyingPrice != '')
                        $orderProduct->setpop_price($buyingPrice);

                    if ($supplierSku != '')
                        $orderProduct->setpop_supplier_sku($supplierSku);
                } 
                else 
                {
                    $orderProduct = $this->_orderProductFactory->create();
                    $orderProduct->setpop_po_id($this->_poId);
                    $orderProduct->setpop_product_id($productId);
                    $orderProduct->setpop_qty($qty);
                    $orderProduct->setpop_qty_received(0);

                    if ($taxRate != '')
                        $orderProduct->setpop_tax_rate($taxRate);

                    if ($buyingPrice != '')
                        $orderProduct->setpop_price($buyingPrice);

                    if ($supplierSku != '')
                        $orderProduct->setpop_supplier_sku($supplierSku);

                    $orderProduct->setpop_change_rate($this->_order->getpo_change_rate());
                }

                $orderProduct->save();

                $result['found'] = $sku;
            }
        } 
        else 
        {
            $result['error'] = __('sku or qty is missing');
        }


        return $result;
    }

    public function checkColumns($columns)
    {
        $mandatory = [
                        0 => 'sku',
                        1 => 'qty'
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
