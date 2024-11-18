<div class="form-group">
    @if(!is_null($tooltip))
        {{!  $label .= '<i class="fa fa-info-circle fa-lg tip-info" data-bs-toggle="tooltip" data-bs-placement="left" title="" data-bs-title="' . $tooltip . '"></i>' }}
    @endif
    <label for="{{ $name }}" class="d-flex flex-row justify-content-between">{!! $label !!}</label>
    {{ Form::text($name, $value, array_merge(['class' => 'form-control'], $attributes)) }}
</div>