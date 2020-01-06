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
            <li>
                <a href="#moduletpl-tool-tab-language-{{ $itemId }}" class="glyphicons font" data-toggle="tab" aria-expanded="true"><i></i>
                    <span>{{ __('moduletpl::messages.languages') }}</span>
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
    <div class="tab-pane" id="moduletpl-tool-tab-language-{{ $itemId }}">
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
</div>


