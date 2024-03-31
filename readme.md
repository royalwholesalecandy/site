[royalwholesalecandy.com](https://royalwholesalecandy.com) (Magento 2).

## How to upgrade Mage2.PRO packages
```
sudo service cron stop           
bin/magento maintenance:enable  
composer remove royalwholesalecandy/core
composer remove royalwholesalecandy/captcha
composer clear-cache
composer require royalwholesalecandy/core:*
composer require royalwholesalecandy/captcha:* 
rm -rf var/di var/generation generated/*
bin/magento setup:upgrade
bin/magento cache:enable
bin/magento setup:di:compile
bin/magento cache:clean
redis-cli FLUSHALL
rm -rf pub/static/*
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme Megnor/mag-child \
	--theme Mgs/organie en_US \
	--theme bs_eren/bs_eren3 \
	-f en_US
bin/magento cache:clean
bin/magento maintenance:disable
sudo service cron start
rm -rf var/log/*
```

## How to deploy the static content
### On localhost
```posh
bin/magento cache:clean
rm -rf pub/static/*
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme bs_eren/bs_eren3 \
	-f en_US
bin/magento cache:clean
```
### On the production server
```
bin/magento maintenance:enable
bin/magento cache:clean
rm -rf pub/static/*
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme bs_eren/bs_eren3 \
	-f en_US
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme Megnor/mag-child \
	-f en_US
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme Mgs/organie en_US \
	-f en_US
bin/magento cache:clean
bin/magento maintenance:disable
```

## How to restart services on the production server
```
...
```
