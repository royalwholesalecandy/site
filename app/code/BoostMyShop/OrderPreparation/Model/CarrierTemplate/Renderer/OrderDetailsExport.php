<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

class OrderDetailsExport extends RendererAbstract
{
    public function getShippingLabelFile($ordersInProgress, $carrierTemplate){

        $content = '';

        //generate the first line of the CSV or the XML header
        if($carrierTemplate->getct_export_file_header())
            $content .= $this->appendTemplate($carrierTemplate->getct_export_file_header(), []);

        foreach($ordersInProgress as $orderInProgress)
        {
            $dataToExport = $orderInProgress->getDatasForExport();

            //order data
            if($carrierTemplate->getct_export_file_order_header())
                $content .= $this->appendTemplate($carrierTemplate->getct_export_file_order_header(), $dataToExport);

            //order product data
            foreach($orderInProgress->getAllItems() as $item)            
                $content .= $this->appendTemplate($carrierTemplate->getct_export_file_order_products(), array_merge($item->getDatasForExport(), $dataToExport));

            //xml order footer (never used for CSV)
            if($carrierTemplate->getct_export_file_order_footer())
                $content .= $this->appendTemplate($carrierTemplate->getct_export_file_order_footer(), $dataToExport);
        }

        //XML footer when necerrary (never used for CSV)
        if($carrierTemplate->getct_export_file_footer())
            $content .= $this->appendTemplate($carrierTemplate->getct_export_file_footer(), []);

        return $content;
    }

    protected function appendTemplate($template, $data)
    {
        //template processing
        $regExp = '*({[^}]+})*';
        preg_match_all($regExp, $template, $result, PREG_OFFSET_CAPTURE);
        foreach ($result[0] as $item) {
            $code = str_replace('{', '', str_replace('}', '', $item[0]));
            if (isset($data[$code]))
                $template = str_replace($item[0], $data[$code], $template);
            else
                $template = str_replace($item[0], '', $template);
        }

        //clean unnecessary spaces
        $template = trim($template);

        //on each append template call, we require to have a line return at the end
        $templateLen = strlen($template);
        $cr = chr(13);
        if($template && $templateLen > 0){
            $lastChar  = $template[$templateLen-1];
            if($lastChar !== $cr)
                    $template .= $cr;
        }

        return $template;
    }

}
