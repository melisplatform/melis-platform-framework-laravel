<?php

namespace MelisPlatformFrameworkLaravel\Helpers;

class LaminasEvent
{
    static public function sendEvent($eventCode, $data = null)
    {
        $srv = app('LaminasServiceManager')->get('MelisGeneralService');

        return $srv->sendEvent($eventCode, ['data' => $data])['data'];
    }
}