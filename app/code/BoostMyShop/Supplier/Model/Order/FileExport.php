<?php

namespace BoostMyShop\Supplier\Model\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class FileExport
{

    protected $_eventManager;

    public function __construct(
        \Magento\Framework\Event\Manager $eventManager
    )
    {
        $this->_eventManager = $eventManager;
    }

    public function getFileName($po, $supplier = false)
    {
        $fileName = null;

        if (is_array($po))
        {
            if ($supplier->getsup_file_name())
            {
                $fileName = $supplier->getsup_file_name();
                $fileName = str_replace('{reference}', 'orders', $fileName);
            }
            else
            {
                return 'purchase_orders.csv';
            }
        }
        else
        {
            if ($po->getSupplier()->getsup_file_name())
            {
                $fileName = $po->getSupplier()->getsup_file_name();
                $fileName = str_replace('{reference}', $po->getpo_reference(), $fileName);
            }
            else
            {
                return 'po_'.$po->getpo_reference().'.csv';
            }
        }

        return $fileName;
    }

    public function getFileContent($po, $supplier = false)
    {
        if (!$supplier)
            $supplier = $po->getSupplier();

        if (!is_array($po))
            $po = [$po];

        if (!$supplier->getsup_file_header() && !$supplier->getsup_file_product())
            return $this->getDefaultFileContent($po);

        $content = '';

        $content .= $this->transformTemplate($supplier->getsup_file_header(), $po)."\r\n";

        foreach($po as $purchaseOrder)
        {
            if ($this->transformTemplate($supplier->getsup_file_order_header(), $purchaseOrder))
                $content .= $this->transformTemplate($supplier->getsup_file_order_header(), $purchaseOrder)."\r\n";

            foreach($purchaseOrder->getAllItems() as $item)
                $content .= $this->transformTemplate($supplier->getsup_file_product(), $purchaseOrder, $item)."\r\n";

            if ($this->transformTemplate($supplier->getsup_file_order_footer(), $purchaseOrder))
                $content .= $this->transformTemplate($supplier->getsup_file_order_footer(), $purchaseOrder)."\r\n";
        }

        if ($this->transformTemplate($supplier->getsup_file_footer(), $po))
            $content .= $this->transformTemplate($supplier->getsup_file_footer(), $po)."\r\n";

        return $content;
    }

    public function transformTemplate($template, $po, $pop = null)
    {
        if (!is_array($po))
        {
            foreach($this->getCodes($po, $pop) as $k => $v)
            {
                $template = str_replace("{".$k."}", $v, $template);
            }
        }
        return $template;
    }

    /**
     * Return available codes
     *
     * @param $po
     * @param $pop
     * @return array
     */
    public function getCodes($po, $pop = null)
    {
        $codes = [];

        //po
        foreach($po->getData() as $k => $v)
        {
            if (!is_array($v) && !is_object($v))
                $codes['po.'.$k] = $v;
        }

        //supplier
        foreach($po->getSupplier()->getData() as $k => $v)
        {
            if (!is_array($v) && !is_object($v))
                $codes['supplier.'.$k] = $v;
        }

        //pop
        if ($pop)
        {
            foreach($pop->getData() as $k => $v)
            {
                if (!is_array($v) && !is_object($v))
                    $codes['item.'.$k] = $v;
            }
        }

        //product
        if ($pop && $pop->getProduct())
        {
            foreach($pop->getProduct()->getData() as $k => $v)
            {
                if (!is_array($v) && !is_object($v))
                    $codes['product.'.$k] = $v;
            }
        }

        //raise an event so other modules (mostly dropship one) can append their own codes
        $obj = new \Magento\Framework\DataObject();
        $obj->setCodes($codes);
        $this->_eventManager->dispatch('bms_supplier_order_export_codes', ['po' => $po, 'pop' => $pop, 'obj' => $obj]);
        $codes = $obj->getCodes();

        return $codes;
    }


    public function getDefaultFileContent($poArray)
    {
        $content = '';

        $columns = ['order', 'pop_sku', 'pop_name', 'pop_supplier_sku', 'pop_qty', 'pop_qty_pack', 'pop_qty_received', 'pop_price', 'pop_discount_percent', 'pop_tax_rate', 'pop_tax', 'pop_subtotal', 'pop_grandtotal', 'pop_eta'];

        $header = [];
        foreach($columns as $column)
            $header[] = '"'.str_replace('pop_', '', $column.'"');
        $content .= implode(',', $header)."\r\n";


        foreach($poArray as $po)
        {
            foreach($po->getAllItems() as $item)
            {
                $line = [];
                foreach($columns as $column)
                {
                    if ($column != 'order')
                        $line[] .= '"'.$item->getData($column).'"';
                    else
                        $line[] .= '"'.$po->getpo_reference().'"';
                }

                $content .= implode(',', $line)."\r\n";
            }
        }

        return $content;
    }


}
