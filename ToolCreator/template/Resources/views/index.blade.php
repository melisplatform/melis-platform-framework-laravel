<div class="me-heading bg-white border-bottom">
    <div class="row">
        <div class="me-hl col-xs-8 col-sm-8 col-md-8">
            <h1 class="content-heading">{{ __('moduletpl::messages.title') }}</h1>
            <p>{{ __('moduletpl::messages.desc') }}</p>
        </div>
        <div class="me-hl col-xs-4 col-sm-4 col-md-4">
            <button class="btn btn-success pull-right moduletpl-add-update-action"><i class="fa fa-plus"></i> {{ __('moduletpl::messages.add_item') }}</button>
        </div>
    </div>
</div>
<div class="innerAll spacing-x2">
    {!! DataTable::createTable(config('moduletpl.table')) !!}

    <div class="modal fade" id="id_moduletpl_generic_modal_tool_container">
        <div class="modal-dialog" role="document">
            <div id="loadingZone" class="overlay-loader">
                <img class="loader-icon spinning-cog" src="/MelisCore/assets/images/cog12.svg" data-cog="cog12">
            </div>
        </div>
    </div>
</div>