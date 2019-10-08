<?php

$isCliReqs = php_sapi_name() == 'cli' ? true : false;

//third party file
$thirdPartyFolder = !$isCliReqs ? $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'thirdparty/Laravel' : 'thirdparty/Laravel';

if (!is_dir($thirdPartyFolder)) {
    return MelisPlatformFrameworks\Support\MelisPlatformFrameworks::downloadFrameworkSkeleton('laravel');
}else{
    return [
        'success' => true,
        'message' => 'Laravel skeleton downloaded successfully'
    ];
}