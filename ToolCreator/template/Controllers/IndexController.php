<?php

namespace Modules\ModuleTpl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Lang;
use Modules\ModuleTpl\Entities\ModelName;
use Modules\ModuleTpl\Http\Requests\ModelNameRequest;

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

    /**
     * Display the form
     *
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function form($id = null)
    {
        $model = null;

        if ($id)
            $model = ModelName::find($id);

        $param = [
            'id' => $id,
            'model' =>  $model
        ];

        return view('moduletpl::form', $param);
    }

    /**
     * This method provide the list of data for the table
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $orderKey = $request->input('columns.'.$request->input('order.0.column').'.data');
        $sortOrder = $request->input('order.0.dir');
        $start = $request->input('start');
        $length =  $request->input('length');
        $search = $request->input('search.value');

        $album = ModelName::select('*', (new ModelName)->getKeyName().' As DT_RowId')
            ->orderBy($orderKey, $sortOrder);

        // Fetching total records from db table
        $totalRecords = $album->get()->count();

        $dataTableConfig = config('laraveltool.table');
        if ($search && $dataTableConfig['searchables'])
            foreach ($dataTableConfig['searchables'] As $col)
                $album->orWhere($col, 'like', '%'.$search.'%');

        // Fetching filtered records from db table
        $recordsFiltered = $album->get()->count();

        // Fetching filtered records with offset and limit from db table
        $data = $album->offset($start)
            ->limit($length)
            ->get();

#TCDISPLAYTABLECOLS

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' =>  $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Saving item
     * This function handle the storing and updating data
     *
     * @param ModelNameRequest $request
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(ModelNameRequest $request, $id = null)
    {
        // Validate
        $request->validated();

        // Album Model
        $model = new ModelName;
        if ($id)
            $model = ModelName::find($id);

        // Fill with Data from input
        $model->fill($request->input());

        // Save
        $model->store();

        // Save action to logs
        $logType = (!$id) ? ModelName::ADD : ModelName::UPDATE;

        $title = Lang::get('moduletpl::messages.save_item');
        $message = Lang::get('moduletpl::messages.'.strtolower($logType).'_item_success');

        $keyName = (new ModelName)->getKeyName();
        $model->logAction(true, $title, $message, $logType, $model->$keyName);

        return response()->json(['success' => 1, 'title' => $title, 'message' => $message]);
    }

    /**
     * This function handle the deletion of data
     *
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id = null)
    {
        $model = ModelName::find($id);
        $model->delete();

        $title = Lang::get('moduletpl::messages.delete_item');
        $message = Lang::get('moduletpl::messages.delete_item_success');

        // Save delete action to logs
        $model->logAction(true, $title, $message, ModelName::DELETE, $id);

        return response()->json(['success' => 1, 'title' => $title, 'message' => $message]);
    }
}
