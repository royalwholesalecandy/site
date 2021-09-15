<?php


namespace Metagento\Referrerurl\Controller\Adminhtml\Export;


class Customer extends
    \Metagento\Referrerurl\Controller\Adminhtml\AbstractController
{
    public function execute()
    {
        $reportData = $this->_session->getData('referrerurl_customer_data');
        $fileName   = "referrerurl_customer_report.csv";
        $handle     = fopen($fileName, 'w');
        fputcsv($handle, [__("Domain"), __("Count")]);
        foreach ( $reportData as $domain => $count ) {
            fputcsv($handle, [$domain, $count]);
        }
        $this->downloadCsv($fileName);
    }

}