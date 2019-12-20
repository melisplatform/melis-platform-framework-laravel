<?php

namespace Modules\ModuleTpl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\ModuleTpl\Entities\ModelName;
use Modules\ModuleTpl\Events\SaveFormEvent;
use Modules\ModuleTpl\Events\DeleteItemEvent;

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

#TCTOOLTYPEEDTION

    /**
     * This method provide the list of data for the table
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $start = $request->input('start');
        $length =  $request->input('length');
        $orderKey = $request->input('columns.'.$request->input('order.0.column').'.data');
        $sortOrder = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $model = new ModelName();
        $list = $model->getList($start, $length, $orderKey, $sortOrder, $search);

        return response()->json($list);
    }

    /**
     * Saving item
     * This function handle the storing and updating data
     *
     * @param Request $request
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request, $id = null)
    {
        $result = event(new SaveFormEvent($id));

        return response()->json($result[0]);
    }

    /**
     * This function handle the deletion of data
     *
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id = null)
    {
        $result = event(new DeleteItemEvent($id));

        return response()->json($result[0]);
    }
}
