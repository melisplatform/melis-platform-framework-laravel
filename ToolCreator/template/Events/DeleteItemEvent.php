<?php

namespace Modules\ModuleTpl\Events;

use Illuminate\Queue\SerializesModels;

class DeleteItemEvent
{
    use SerializesModels;

    public $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }
}