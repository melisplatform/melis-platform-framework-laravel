<div class="modal-content" id="id_moduletpl_generic_modal_tool">
    <div class="modal-body padding-none">
        <div class="wizard">
            <div class="widget widget-tabs widget-tabs-double widget-tabs-responsive margin-none border-none">
                <div class="widget-head">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#moduletpl-tool-modal-main" class="glyphicons {{ $id ? 'pencil' : 'plus' }}" data-toggle="tab" aria-expanded="true"><i></i>
                                {{ __('moduletpl::messages.properties') }}
                            </a>
                        </li>
                        <li>
                            <a href="#moduletpl-tool-modal-language" class="glyphicons font" data-toggle="tab" aria-expanded="true"><i></i>
                                {{ __('moduletpl::messages.languages') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="widget-body innerAll inner-2x">
                    <div class="tab-content moduletpl-form-container">
                        <div class="tab-pane active" id="moduletpl-tool-modal-main">
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
                        <div class="tab-pane" id="moduletpl-tool-modal-language">
                            <div class="row">

                                <div class="col-xs-12 col-md-3">
                                    <div class="product-text-tab-container">
                                        <ul class="nav-tabs product-text-tab">
                                            @foreach($langs As $key => $lang)
                                                <li class="{{ ($key) ? '':'active' }}">
                                                    <a class="clearfix" data-toggle="tab" href="#moduletpl-text-translation-{{ $lang['lang_cms_locale'] }}" aria-expanded="false">
                                                        @php
                                                            $langLabel = '<span>'. $lang['lang_cms_name'] .'</span>';
                                                            $moduleSvc = app('ZendServiceManager')->get('ModulesService');
                                                            if (file_exists($moduleSvc->getModulePath('MelisCms').'/public/images/lang-flags/'.$lang['lang_cms_locale'].'.png')){
                                                                $langLabel .= '<span class="pull-right"><img src="/MelisCms/images/lang-flags/'.$lang['lang_cms_locale'].'.png"></span>';
                                                            }
                                                        @endphp
                                                        {!! $langLabel !!}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-9">
                                    <div class="tab-content">

                                        @foreach($langs As $key => $lang)

                                        <div id="moduletpl-text-translation-{{ $lang['lang_cms_locale'] }}" class="tab-pane {{ ($key) ? '':'active' }}">

                                            @php
                                                $formAttr = ['id' => 'moduletpl-lang-form-'.$lang['lang_cms_id'], 'data-lang-id' => $lang['lang_cms_id'], 'class' => 'moduletpl-lang-form'];
                                                $data = null;
                                                $defaultData['cnews_lang_id'] = $lang['lang_cms_id'];
                                            @endphp

                                            @if(!empty($model->languages))
                                                @foreach($model->languages As $slang)
                                                    @if($slang->cnews_lang_id == $lang['lang_cms_id'])
                                                        @php $data = $slang @endphp
                                                        @break
                                                    @endif
                                                @endforeach
                                            @endif

                                            @if($data)
                                                {{ Form::model($data, $formAttr) }}
                                            @else
                                                {{ Form::open($formAttr) }}
                                            @endif

                                            {!! Form::melisFieldRow(config('moduletpl.form.languages'), $data, $defaultData) !!}

                                            {!! Form::close() !!}

                                        </div>

                                        @endforeach
                                    </div>
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