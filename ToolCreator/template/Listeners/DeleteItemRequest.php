<?php

namespace Modules\ModuleTpl\Listeners;


use MelisPlatformFrameworkLaravel\Entities\GenericModel;
use Modules\ModuleTpl\Events\DeleteItemEvent;

class DeleteItemRequest
{
    public $event;

    public $deleteRequests = [
       \Modules\ModuleTpl\Http\Requests\ModelNameRequest::class,#TCLANGREQUEST
    ];

    public function handle(DeleteItemEvent $event)
    {
        $this->event = $event;

        $instances = [];

        foreach ($this->deleteRequests As $request) {
            $instances[] =  new $request;
        }

        foreach ($instances As $request) {
            $request->delete($this);
        }

        return $this->logAction($event);
    }

    public function logAction($event)
    {
        $itemId = $event->id ?? null;

        $title = __('moduletpl::messages.delete_item');
        $message = __('moduletpl::messages.delete_item_success');

        // Save delete action to logs
        (new GenericModel())->logAction(true, $title, $message, GenericModel::DELETE, $itemId);

        return [
            'success' => true,
            'title' => $title,
            'message' => $message
        ];
    }
}