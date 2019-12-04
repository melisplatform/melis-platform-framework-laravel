<div class="form-group">
    @if(!is_null($tooltip) && !empty($tooltip))
        {{!  $label .= '<i class="fa fa-info-circle fa-lg pull-right tip-info" data-toggle="tooltip" data-placement="left" title="" data-original-title="' . $tooltip . '"></i>' }}
    @endif
    <label for="{{ $name }}" class="control-label">{!! $label !!}</label>
    {{ Form::text($name, $value, array_merge(['class' => 'form-control'], $attributes)) }}
</div>