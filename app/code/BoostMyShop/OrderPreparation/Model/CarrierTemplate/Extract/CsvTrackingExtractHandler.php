<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Extract;

class CsvTrackingExtractHandler extends ExtractAbstract
{
    public function extract($data, $carrierTemplate)
    {
        $datas = [];

        $shipmentIndex = $carrierTemplate->getct_import_file_shipment_reference_index() - 1;
        $trackingIndex = $carrierTemplate->getct_import_file_tracking_index() - 1;
        $separator = $carrierTemplate->getct_import_file_separator();

        $data = str_replace("\r","\n",$data);
        $data = str_replace("\n\n","\n",$data);
        $lines = explode("\n", $data);
        
        $count = 0;
        foreach($lines as $line)
        {
            if ($carrierTemplate->getct_import_file_skip_first_line() && ($count == 0))
            {
                $count++;
                continue;
            }

            $data = ['success' => true, 'shipment' => '', 'tracking' => '', 'msg' => '', 'source' => $line];
            try
            {
                $fields = explode($separator, $line);
                if (!isset($fields[$shipmentIndex]))
                    throw new \Exception('Shipment index does not exist');
                if (!isset($fields[$trackingIndex]))
                    throw new \Exception('Tracking index does not exist');
                $data['shipment'] = $this->clean($fields[$shipmentIndex]);
                $data['tracking'] = $this->clean($fields[$trackingIndex]);
            }
            catch(\Exception $ex)
            {
                $data['success'] = false;
                $data['msg'] = $ex->getMessage();
            }

            $datas[] = $data;
            $count++;
        }

        return $datas;
    }

    public function clean($txt)
    {
        $txt = trim($txt);
        return $txt;
    }
}
