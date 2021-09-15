#Installation

Amazon Connector For Magento2 module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and AmazonMagentoConnect(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/AmazonMagentoConnect/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
composer require guzzlehttp/guzzle:~6.0
composer require league/csv:8.2.2
composer require spatie/array-to-xml
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy 

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

Amazon Connector For Magento2 module's working process follow user guide - http://webkul.com/blog/amazon-connector-magento2/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
