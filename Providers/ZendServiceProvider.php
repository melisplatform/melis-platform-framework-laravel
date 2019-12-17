<?php

namespace MelisPlatformFrameworkLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Zend\Session\Container;

class ZendServiceProvider extends ServiceProvider
{
    public $zendServiceManager;
    public $zendEventManager;

    const ZEND_DISK = 'zend_public';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ZendServiceManager', function(){
            return $this->zendServiceManager;
        });

        $this->app->singleton('ZendEventManager', function(){
            return $this->zendEventManager;
        });

        $this->app->register(ModuleCreatorServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->zendApplication();
        $this->syncDatabaseConnection();
        $this->setLocale();
        $this->addMelisPublic();
    }

    /**
     * Executing Zend application to retrieving
     * Service and Event managers
     */
    public function zendApplication()
    {
        // Avoid accessing from artisan command
        $zendApp = $_SERVER['DOCUMENT_ROOT'].'/../config/application.config.php';
        if (file_exists($zendApp)){
            //Executing Zend application
            $zendApplication = \Zend\Mvc\Application::init(require $zendApp);

            // Zend Service Manager
            $this->zendServiceManager = $zendApplication->getServiceManager();
            // Zend Event Manager
            $this->zendEventManager = $zendApplication->getEventManager();
        }
    }

    /**
     * This method set the Database connection
     * using Zend configuration and
     * connection
     */
    public function syncDatabaseConnection()
    {
        if (!$this->zendServiceManager)
            return;

        // Retrieving Zend application database connection from config
        $config = $this->zendServiceManager->get('config');

        if (!empty($config['db'])){

            $dbConnection = explode(';', $config['db']['dsn']);

            $driver = explode(':', $dbConnection[0])[0];
            $host = explode('=', $dbConnection[1])[1];
            $database = explode('=', $dbConnection[0])[1];
            $username = $config['db']['username'];
            $password = $config['db']['password'];

            /**
             * Assign database configuration data collected to
             * laravel application using global Config helper
             */
            config([
                'database.connections.mysql' => [
                    'driver' => $driver,
                    'host' => $host,
                    'database' => $database,
                    'username' => $username,
                    'password' => $password,
                ]
            ]);
        }
    }

    /**
     * Adding custom config for
     * disk root
     */
    public function addMelisPublic()
    {
        config([
            'filesystems.disks.'. self::ZEND_DISK => [
                'driver' => 'local',
                'root'   => __DIR__. '/../../../../public/media',
            ]
        ]);
    }

    /**
     * This method set Localzation
     * using Zend configuration from session
     */
    public function setLocale()
    {
        $locale = new Container('meliscore');
        $this->app->setLocale(explode('_', $locale['melis-lang-locale'])[0]);
    }
}
