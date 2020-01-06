@php
    $itemId = $id ?? 0;
@endphp
<div class="me-heading bg-white border-bottom">
    <div class="row">
        <div class="me-hl col-xs-8 col-sm-8 col-md-8">
            <h1 class="content-heading">{{  ($itemId) ? __('moduletpl::messages.title'). ' / '.$id : __('moduletpl::messages.common_add')}}</h1>
        </div>
        <div class="me-hl col-xs-4 col-sm-4 col-md-4">
            <button class="btn btn-success pull-right moduletpl-btn-save-action" data-id="{{ $itemId }}">{{ __('moduletpl::messages.common_save') }}</button>
        </div>
    </div>
</div>
<div class="widget widget-tabs widget-tabs-double-2 widget-tabs-responsive">
    <div class="widget-head nav">
        <ul class="tabs-label nav-tabs">
            <li class="active">
                <a href="#moduletpl-tool-tab-{{ $itemId }}" class="glyphicons tag" data-toggle="tab" aria-expanded="true"><i></i>
                    <span>{{ __('moduletpl::messages.properties') }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="tab-content innerAll spacing-x2 moduletpl-form-container-{{ $itemId }}">
    <div class="tab-pane active" id="moduletpl-tool-tab-{{ $itemId }}">
        <div class="row">
            <div class="col-md-12">
                @php
                    $formAttr = ['id' => 'moduletpl-form'];
                @endphp

                @if($model)
                    {{ Form::model($model, $formAttr) }}
                @else
                    {{ Form::open($formAttr) }}
                @endif

                {!! Form::melisFieldRow(config('moduletpl.form.properties'), $model) !!}

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>


