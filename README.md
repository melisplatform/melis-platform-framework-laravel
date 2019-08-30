# melis-platform-framework-laravel

This laravel service provides a connection to the Zend application enabling 
access to the Service and Event Manager and Database connection configuration of the 
application.

### Prerequisites
This module requires melisplatform/melis-core.
It will automatically be done when using composer.

### Installing
```
composer require melisplatform/melis-platform-framework-laravel
```

### Service Providers
Activating the Service provider by just adding to the config/app.php file in the 
Service Providers section.
```
MelisPlatformFrameworkLaravel\ZendServiceProvider::class
```

### Usage
Below is an example of direct calling a Model in laravel controller

```
$languagesTbl = app('ZendServiceManager')->get('MelisCoreTableLang');
$listArray = $languagesTbl->fetchAll();
```
