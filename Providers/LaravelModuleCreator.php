<?php

namespace MelisPlatformFrameworkLaravel;

class LaravelModuleCreator
{
    const LARAVEL_MODULES_DIR = __DIR__ . '/../../../../thirdparty/Laravel/Modules';

    static function run($param)
    {
        print_r($param);
    }
}