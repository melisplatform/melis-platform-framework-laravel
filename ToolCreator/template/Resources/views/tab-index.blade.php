<div class="me-heading bg-white border-bottom">
    <div class="row">
        <div class="me-hl col-xs-8 col-sm-8 col-md-8">
            <h1 class="content-heading">{{ __('moduletpl::messages.title') }}</h1>
            <p>{{ __('moduletpl::messages.desc') }}</p>
        </div>
        <div class="me-hl col-xs-4 col-sm-4 col-md-4">
            <button class="btn btn-success pull-right moduletpl-add-update-action">{{ __('moduletpl::messages.common_add') }}</button>
        </div>
    </div>
</div>
<div class="innerAll spacing-x2">
    {!! DataTable::createTable(config('moduletpl.table')) !!}
</div>