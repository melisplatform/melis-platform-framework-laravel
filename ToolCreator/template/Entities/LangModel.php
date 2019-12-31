<?php

namespace Modules\ModuleTpl\Entities;

use MelisPlatformFrameworkLaravel\Entities\GenericModel;
use MelisPlatformFrameworkLaravel\Providers\ZendServiceProvider;

class ModelLangName extends GenericModel
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
     * The foreign key to the Main table
     */
    const MAIN_FOREIGN_KEY = '#TCFK1';

    /**
     * The foreign key to the CMS language table
     */
    const LANG_FOREIGN_KEY = '#TCFK2';

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
    public function store($form)
    {
        // Store data
        $this->save();

        #TCCALLSTOREFILE
    }

#TCSTOREFILEFUNCTION
}