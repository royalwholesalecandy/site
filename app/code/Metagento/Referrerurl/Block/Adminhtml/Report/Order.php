<?php


namespace Metagento\Referrerurl\Block\Adminhtml\Report;


class Order extends
    \Metagento\Referrerurl\Block\Adminhtml\AbstractBlock
{

    public function getReportData()
    {
        $orders = $this->getCollection();
        $data   = array();
        foreach ( $orders as $order ) {
            $referrerUrl = $order->getData('referrer_url');
            // fake url
            if ( strpos($referrerUrl, 'http') === false ) {
                $referrerUrl = 'http://' . $referrerUrl;
            }
            $url    = parse_url($referrerUrl);
            $domain = $url['host'];
            if ( array_key_exists($domain, $data) ) {
                $data[$domain] += 1;
            } else {
                $data[$domain] = 1;
            }
        }
        arsort($data);
        $this->_backendSession->setData('referrerurl_order_data',$data);
        return $data;
    }

    public function getExportUrl()
    {
        return $this->_urlBuilder->getUrl('referrerurl/export/order');
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getCollection()
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->orderFactory->create()->getCollection();
        $collection->addFieldToFilter('referrer_url', array('notnull' => true));
        return $collection;
    }
}