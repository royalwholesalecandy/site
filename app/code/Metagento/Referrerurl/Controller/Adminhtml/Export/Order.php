<?php


namespace Metagento\Referrerurl\Controller\Adminhtml\Export;


class Order extends
    \Metagento\Referrerurl\Controller\Adminhtml\AbstractController
{
    public function execute()
    {
        $reportData = $this->_session->getData('referrerurl_order_data');
        $fileName   = "referrerurl_order_report.csv";
        $handle     = fopen($fileName, 'w');
        fputcsv($handle, [__("Domain"), __("Count")]);
        foreach ( $reportData as $domain => $count ) {
            fputcsv($handle, [$domain, $count]);
        }
        $this->downloadCsv($fileName);
    }

}