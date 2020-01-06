<?php

namespace Modules\ModuleTpl\Listeners;


use MelisPlatformFrameworkLaravel\Entities\GenericModel;
use Modules\ModuleTpl\Events\SaveFormEvent;

class SaveFormRequest
{
    public $event;

    public $formRequests = [
        \Modules\ModuleTpl\Http\Requests\ModelNameRequest::class,#TCLANGREQUEST
    ];

    public function handle(SaveFormEvent $event)
    {
        $this->event = $event;

        $instances = [];

        foreach ($this->formRequests As $request) {
            $instances[] =  new $request;
        }

        $errors = [];
        foreach ($instances As $request) {
            $res = $request->validate($this);
            if (!is_bool($res))
                $errors = array_merge($errors, $res);
        }

        if (empty($errors)){
            foreach ($instances As $request) {
                $request->store($this);
            }
        }

        $validated = (empty($errors)) ? true : false;

        $result = array_merge(
            [
                'success' => $validated,
                'id' => $event->id
            ],
            $this->logAction($event, $validated)
        );

        if (!empty($errors))
            $result = array_merge($result, ['errors' => $errors]);

        return $result;
    }

    public function logAction($event, $validated)
    {
        // Save action to logs
        $itemId = $event->id ?? null;
        $logType = ($itemId) ? GenericModel::ADD : GenericModel::UPDATE;

        $actionResult = ($validated) ? 'item_success' : 'failed';

        $title = __('moduletpl::messages.save_item');
        $message = __('moduletpl::messages.' . strtolower($logType) . '_' . $actionResult);

        // Album Model
        (new GenericModel())->logAction($validated, $title, $message, $logType, $itemId);

        return [
            'title' => $title,
            'message' => $message
        ];
    }
}