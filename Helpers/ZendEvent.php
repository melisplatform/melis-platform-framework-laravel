<?php

namespace MelisPlatformFrameworkLaravel\Helpers;

class ZendEvent
{
    static public function sendEvent($eventCode, $data = null)
    {
        $coreSrv = app('ZendServiceManager')->get('MelisCoreGeneralService');

        return $coreSrv->sendEvent($eventCode, ['data' => $data])['data'];
    }
}