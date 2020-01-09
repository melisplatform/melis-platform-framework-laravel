<?php

namespace Modules\ModuleTpl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Newstooltab\Entities\MelisCmsNews;
use Modules\Newstooltab\Events\SaveFormEvent;
use Modules\Newstooltab\Events\DeleteItemEvent;

class IndexController extends Controller
{
    /**
     * Display the tool container
     *
     * @return Response
     */
    public function index()
    {
        return view('moduletpl::index');
    }
}
