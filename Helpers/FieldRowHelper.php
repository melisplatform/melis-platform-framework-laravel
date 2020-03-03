<?php

namespace MelisPlatformFrameworkLaravel\Helpers;

use Illuminate\Support\Facades\Lang;

class FieldRowHelper
{
    static public function createFields($formConfig, $data = [], $defaultData = [])
    {
        if (!is_array($formConfig))
            return null;

        $fieldSet = null;

        foreach ($formConfig As $name => $opts){

            $options = (!empty($opts['options'])) ? $opts['options'] : [];
            $attributes = (!empty($opts['attributes'])) ? $opts['attributes'] : [];

            $srv = app('ZendServiceManager')->get('FormElementManager');

            $finalOptions = [];

            if ($options)
                $finalOptions = array_merge($finalOptions, $options);

            $finaleAttributes = [
                'class' => 'form-control'
            ];

            if ($attributes)
                $finaleAttributes = array_merge($finaleAttributes, $attributes);

            $type = (!empty($opts['type'])) ? $opts['type'] : 'MelisText';

            $element = $srv->get($type);

            $element->setName($name);

            if (!empty($defaultData))
                if (!empty($defaultData[$name]))
                    $element->setValue($defaultData[$name]);

            $element->setOptions($finalOptions);
            $element->setAttributes($finaleAttributes);

            if (isset($data->$name))
                $element->setValue($data->$name);

            $viewHelper = app('ZendServiceManager')->get('ViewHelperManager');
            $fieldRow = $viewHelper->get('MelisFieldRow');

            $fieldSet .= $fieldRow->render($element).PHP_EOL;
        }

        return $fieldSet;
    }
}