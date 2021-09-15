<?php


namespace Metagento\Referrerurl\Block\Adminhtml\Report;


class Customer extends
    \Metagento\Referrerurl\Block\Adminhtml\AbstractBlock
{

    /**
     * @return array
     */
    public function getReportData()
    {
        $customerCollection = $this->getCollection();
        $data               = array();
        foreach ( $customerCollection as $customer ) {
            $referrerUrl = $customer->getData('referrer_url');
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
        $this->_backendSession->setData('referrerurl_customer_data', $data);
        return $data;
    }

    /**
     * @return string
     */
    public function getExportUrl()
    {
        return $this->_urlBuilder->getUrl('referrerurl/export/customer');
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCollection()
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->customerFactory->create()->getCollection();
        $collection->addAttributeToSelect('referrer_url')
                   ->addAttributeToFilter('referrer_url', array('notnull' => true));
        return $collection;
    }
}