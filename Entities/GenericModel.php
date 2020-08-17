<?php

namespace MelisPlatformFrameworkLaravel\Entities;

use Illuminate\Database\Eloquent\Model;

class GenericModel extends Model
{
    /**
     * Action log types
     */
    const ADD = 'ADD';
    const UPDATE = 'UPDATE';
    const DELETE= 'DELETE';

    /**
     * Saving action to logs using Melis Core Service
     *
     * @param $result
     * @param $title
     * @param $message
     * @param $logType
     * @param $itemId
     */
    public function logAction($result, $title, $message, $logType, $itemId)
    {
        $flashMessenger = app('LaminasServiceManager')->get('MelisCoreFlashMessenger');

        $icon = ($result) ? $flashMessenger::INFO:  $flashMessenger::WARNING;

        $logType = 'MELIS_LARAVEL_LARAVELTOOL_'.$logType;

        $flashMessenger->addToFlashMessenger($title, $message, $icon);

        $logSrv = app('LaminasServiceManager')->get('MelisCoreLogService');
        $logSrv->saveLog($title, $message, $result, $logType, $itemId);
    }
}