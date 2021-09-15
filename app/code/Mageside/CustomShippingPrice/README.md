Magento 2 CustomShippingPrice by Mageside
=========================================

####Magento Support
    v1.0.7 - Magento 2.0.* - 2.3.*

####Change list
    v1.0.7 - Magento 2.7 compatibility checking (updated composer.json)
    v1.0.6 - Fixed functionality if payment method is credit card
    v1.0.4 - Magento 2.2 compatibility checking (updated composer.json)
    v1.0.3 - Updated composer.json
    v1.0.1 - Updated admin section
    v1.0.0 - Start project

####Installation
    1. Download the archive.
    2. Make sure to create the directory structure in your Magento - 'Magento_Root/app/code/Mageside/CustomShippingPrice'.
    3. Unzip the content of archive to directory 'Magento_Root/app/code/Mageside/CustomShippingPrice'
       (use command 'unzip ArchiveName.zip -d path_to/app/code/Mageside/CustomShippingPrice').
    4. Run the command 'php bin/magento module:enable Mageside_CustomShippingPrice' in Magento root.
       If you need to clear static content use 'php bin/magento module:enable --clear-static-content Mageside_CustomShippingPrice'.
    5. Run the command 'php bin/magento setup:upgrade' in Magento root.
    6. Run the command 'php bin/magento setup:di:compile' if you have a single website and store, 
       or 'php bin/magento setup:di:compile-multi-tenant' if you have multiple ones.
    7. Clear cache: 'php bin/magento cache:clean', 'php bin/magento cache:flush'
    8. Deploy static content: 'php bin/magento setup:static-content:deploy'