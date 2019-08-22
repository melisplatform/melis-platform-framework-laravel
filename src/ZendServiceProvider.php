<?php

namespace MelisPlatformFrameworkLaravel;

use Illuminate\Support\ServiceProvider;

class ZendServiceProvider extends ServiceProvider
{
    public $zendServiceManager;
    public $zendEventManager;

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
    }

    public function zendApplication()
    {
        // Avoid accessing from artisan command
        $zendApp = $_SERVER['DOCUMENT_ROOT'].'/../config/application.config.php';
        if (file_exists($zendApp)){
            $zendApplication = \Zend\Mvc\Application::init(require $zendApp);

            $this->zendServiceManager = $zendApplication->getServiceManager();
            $this->zendEventManager = $zendApplication->getEventManager();
        }
    }

    public function syncDatabaseConnection()
    {
        if (!$this->zendServiceManager)
            return;

        $config = $this->zendServiceManager->get('config');

        if (!empty($config['db'])){

            $dbConnection = explode(';', $config['db']['dsn']);

            $driver = explode(':', $dbConnection[0])[0];
            $host = explode('=', $dbConnection[1])[1];
            $database = explode('=', $dbConnection[0])[1];
            $username = $config['db']['username'];
            $password = $config['db']['password'];

            config([
                'database.connections.mysql.driver' => $driver,
                'database.connections.mysql.host' => $host,
                'database.connections.mysql.database' => $database,
                'database.connections.mysql.username' => $username,
                'database.connections.mysql.password' => $password,
            ]);
        }
    }
}
