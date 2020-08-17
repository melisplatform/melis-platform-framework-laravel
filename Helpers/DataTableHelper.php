<?php

namespace MelisPlatformFrameworkLaravel\Helpers;

class DataTableHelper
{
    static public function createTable($tableConfig)
    {
        /**
         * Melis View helper generating the html table structure to be
         * initialize with Data Table
         */
        $dataTableHelper = app('LaminasServiceManager')->get('ViewHelperManager')->get('MelisDataTable');

        return $dataTableHelper->createTable($tableConfig);
    }
}