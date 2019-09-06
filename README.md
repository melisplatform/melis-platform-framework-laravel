# melis-platform-framework-laravel

This laravel service provides a connection to the Zend application enabling 
access to the Service and Event Manager and Database connection configuration of the 
application.

### Prerequisites
This module requires:
* melisplatform/melis-core:^3.1
* laravel/framework:5.8.*

It will automatically be done when using composer.

### Installing
```
composer require melisplatform/melis-platform-framework-laravel
```

### Service Providers
Activating the Service provider by just adding to the ``config/app.php`` file in the 
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

### Where to find Melis Services
- Melis Services are found inside each Melis Modules and these melis modules can be found by following the path below.
```
/_docroot_/vendor/melisplatform/
```
- Inside each Melis Module you can find module.config.php in the config folder. <br />
The module.config.php contains an array keys called **aliases** and **factories** under **service_manager**.

```
'service_manager' => array(
    'invokables' => array(
        
    ),
    'aliases' => array(
        'translator' => 'MvcTranslator',
        'MelisCmsNewsTable' => 'MelisCmsNews\Model\Tables\MelisCmsNewsTable',
        'MelisCmsNewsTextsTable' => 'MelisCmsNews\Model\Tables\MelisCmsNewsTextsTable',
    ),
    'factories' => array(
        //services
        'MelisCmsNewsService' => 'MelisCmsNews\Service\Factory\MelisCmsNewsServiceFactory',
        
        //tables
        'MelisCmsNews\Model\Tables\MelisCmsNewsTable' => 'MelisCmsNews\Model\Tables\Factory\MelisCmsNewsTableFactory',
        'MelisCmsNews\Model\Tables\MelisCmsNewsTextsTable' => 'MelisCmsNews\Model\Tables\Factory\MelisCmsNewsTextsTableFactory',
    ),
),
```
- The array keys inside **aliases** or **factories** can be called in Selix using the MelisServiceProvider.
```
$melisNewsSvc = $app['melis.services']->getService("MelisCmsNewsService");
```

## Authors
* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-core/contributors) who participated in this project.


## License
This project is licensed under the OSL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details