<div class="modal-content" id="id_moduletpl_generic_modal_tool">
    <div class="modal-body padding-none">
        <div class="wizard">
            <div class="widget widget-tabs widget-tabs-double widget-tabs-responsive margin-none border-none">
                <div class="widget-head">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#id-moduletpl-tool-modal" class="glyphicons {{ $id ? 'pencil' : 'plus' }}" data-toggle="tab" aria-expanded="true"><i></i>
                                {{ __('moduletpl::messages.properties') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="widget-body innerAll inner-2x">
                    <div class="tab-content">
                        <div class="tab-pane active" id="id-moduletpl-tool-modal">
                            <div class="row">
                                <div class="col-md-12">
                                    {{! $formAttr = ['url' => '/melis/moduletpl/save/'.$id , 'method' => 'post', 'id' => 'moduletpl-album-form'] }}
                                    @if($model)
                                        {{ Form::model($model, $formAttr) }}
                                    @else
                                        {!! Form::open($formAttr) !!}
                                    @endif

                                    {!! Form::melisFieldRow(config('moduletpl.form.properties'), $model) !!}

                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                        <div align="right">
                            <a data-dismiss="modal" class="btn btn-danger pull-left"><i class="fa fa-times"></i> {{ __('moduletpl::messages.common_close') }}</a>
                            <a class="btn btn-success moduletpl-btn-save-action" {{ $id ? 'data-id=' .$id : '' }}><i class="fa fa-save"></i>  {{ __('moduletpl::messages.common_save') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>