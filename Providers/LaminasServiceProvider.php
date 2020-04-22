<?php

namespace MelisPlatformFrameworkLaravel\Providers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Laminas\Mvc\Application;
use Laminas\Session\Container;
use Laminas\Stdlib\ArrayUtils;

class LaminasServiceProvider extends ServiceProvider
{
    const APP_DISK = 'laminas_app_public';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Laminas not executed when using Artisan cli
         * this because artisan access diredtly to Laravel
         * and by passing the Laminas application
         */
        if (!$this->app->has('LaminasServiceManager'))
            return;

        $this->syncDatabaseConnection();
        $this->setLocale();
        $this->addMelisPublic();
    }

    /**
     * This method set the Database connection
     * using Laminas configuration and
     * connection
     */
    public function syncDatabaseConnection()
    {
        // Retrieving Laminas application database connection from config
        $config = app('LaminasServiceManager')->get('config');

        if (!empty($config['db'])){

            $host       = $config['db']['hostname'];
            $database   = $config['db']['database'];
            $username   = $config['db']['username'];
            $password   = $config['db']['password'];

            /**
             * Assign database configuration data collected to
             * laravel application using global Config helper
             */
            config([
                'database.connections.mysql' => [
                    'driver'    => 'mysql', // Driver mysql supported only
                    'host'      => $host,
                    'database'  => $database,
                    'username'  => $username,
                    'password'  => $password,
                    'charset'   => 'utf8',
                    'collation' => 'utf8_general_ci',
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
            'filesystems.disks.'. self::APP_DISK => [
                'driver' => 'local',
                'root'   => __DIR__. '/../../../../public/media',
            ]
        ]);
    }

    /**
     * This method set Localzation
     * using Laminas configuration from session
     */
    public function setLocale()
    {
        $locale = new Container('meliscore');
        $this->app->setLocale(explode('_', $locale['melis-lang-locale'])[0]);

        // Set current language id to laravel session
        session(['melis-lang-id' => $locale['melis-lang-id']]);

    }
}
