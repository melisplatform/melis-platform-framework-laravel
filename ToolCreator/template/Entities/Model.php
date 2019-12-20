<?php

namespace Modules\ModuleTpl\Entities;

use MelisPlatformFrameworkLaravel\Entities\GenericModel;
use MelisPlatformFrameworkLaravel\Helpers\ZendEvent;

class ModelName extends GenericModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '#TCTABLE';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = '#TCKEYNAME';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        #TCFILLABLE
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const CREATED_AT = null;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * Store data
     */
    public function store()
    {
        // Store data
        $this->save();

        #TCCALLSTOREFILE
    }

#TCSTOREFILEFUNCTION

#TCLANGRELATION

    public function getList($start = 0, $length = 10, $orderKey = null, $sortOrder = 'ASC', $search = null)
    {
#TCSELECT

        // Fetching total records from db table
        $totalRecords = $select->get()->count();

        // Fetching filtered records from db table
        $recordsFiltered = $select->get()->count();

        // Fetching filtered records with Order, offset and limit from db table
        $data = $select->orderBy($orderKey, $sortOrder)
            ->offset($start)
            ->limit($length)
            ->get();

#TCDISPLAYTABLECOLS

        return [
            'draw' => (int) request()->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' =>  $recordsFiltered,
            'data' => $data,
        ];
    }

#TCJOINMETHODS
}