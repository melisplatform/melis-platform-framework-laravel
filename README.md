# melis-platform-framework-laravel

This laravel service provides the a connection to the Zend application to enable 
accessing the Service and Event Manager and Database connection configuration of the 
application

### Prerequisites
This module required melisplatform/melis-core in order to have this module running.
This will automatically be done when using composer.

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
Here's an example of direct calling of Model in laravel controller

```
$languagesTbl = app('ZendServiceManager')->get('MelisCoreTableLang');
$listArray = $languagesTbl->fetchAll();
```