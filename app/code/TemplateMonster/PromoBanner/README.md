### How to install:

1 First add repo to composer.json of magento.

"repositories": [
        {
            "type": "vcs",
            "url": "http://products.git.devoffice.com/magento/promo-banner.git"
        }
    ],

2 Run command:

bin/magento cache:disable

composer require templatemonster/promo-banner:dev-default

3 Run command:

bin/magento module:enable --clear-static-content TemplateMonster_PromoBanner

bin/magento setup:upgrade


### How to remove module:

1 Run command:

bin/magento module:disable --clear-static-content TemplateMonster_PromoBanner

2 Run command:

composer remove promo-banner

