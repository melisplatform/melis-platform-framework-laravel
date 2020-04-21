<?php

namespace MelisPlatformFrameworkLaravel\Helpers;

class LaravelEvent
{
    static public function sendEvent($eventCode, $data = null)
    {
        $srv = app('LaminasServiceManager')->get('MelisGeneralService');

        return $srv->sendEvent($eventCode, ['data' => $data])['data'];
    }
}