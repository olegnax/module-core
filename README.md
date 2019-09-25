# Olegnax Core Extension for Magento 2

This extension is required to be installed for work of any other Olegnax extension or theme for Magento 2.
How to Install Magento 2 Extension Manually and using Composer: https://olegnax.com/documentation/magento-2/how-to-install-magento-2-extension/

## 1. Install Olegnax Core Module via Composer

To install Magento 2 extensions via Composer you should have SSH access and Composer installed on your server.

1. Log in to your server with SSH and navigate to your Magento folder.
2. Use following command to get the latest version of extension:

```
composer require olegnax/module-core
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

More details: https://olegnax.com/documentation/magento-2/how-to-install-magento-2-extension/


## 2. Update Olegnax Core Module

```
composer update olegnax/module-core
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```