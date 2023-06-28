@php
    $element = $element ?? $form['elements'][$key];
    $attributes = [
        'class' => 'form-control rounded-0' . ' ' .($element['attr']['class'] ?? ''), 
        'placeholder' =>  ($element['attr']['placeholder'] ?? $element['label']),
        'aria-invalid' =>  'false',
        'aria-describedby' =>  $key . '-error',
        'data-default' => \is_string($element['value']) ? $element['value'] : (\is_array($element['value']) ? collect($element['value'])->toJson() : ''),
    ] + $element['attr'];
    $element['value'] = ($element['serializeValue'] ?? false) && \is_array($element['value']) ? collect($element['value'])->toJson() : $element['value'];
@endphp
@if($element['label']) {{ html()->label($element['label'])->for($key)->class('col-form-label') }} @endif
@switch($element['type'])
    @case('month')
    @php $options = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Carbon\Carbon::createFromFormat('m', $m)->format('F')]) @endphp
    @php unset($attributes['placeholder']) @endphp
    {{ html()->select($key)->value($element['value'])->options($element['options'])->attributes($attributes) }}
    @break
    @case('select')
    @php $options = collect($element['options']) @endphp
    @php $optionsAttributes = $element['options_attr'] ?? [] @endphp
    @php unset($attributes['placeholder']) @endphp
    {{ html()->select($key)->value($element['value'])->options($element['options'])->attributes($attributes) }}
    @break
    @case('multiselect')
    {{-- @php $attributes['multiple'] = 'multiple' @endphp --}}
    @php unset($attributes['placeholder']) @endphp
    {{ html()->select($key)->value($element['value'])->options($element['options'])->attributes($attributes)->multiple() }}
    @break
    @case('select2')
    @php $attributes['class'] .= ' select2' @endphp
    @php $options = collect($element['options'])->prepend('Select...', '') @endphp
    @php unset($attributes['placeholder']) @endphp
    {{ html()->select($key)->value($element['value'])->options($element['options'])->attributes($attributes) }}
    @break
    @case('multiselect2')
    @php $attributes['class'] .= ' select2' @endphp
    {{-- @php $attributes['multiple'] = 'multiple' @endphp --}}
    @php unset($attributes['placeholder']) @endphp
    {{ html()->select($key)->value($element['value'])->options($element['options'])->attributes($attributes)->multiple() }}
    @break
    @case('radio')
    <div class="row">
        <div class="col-md-12">
        @foreach($element['options'] as $optionValue => $optionText)
        @php $elementId = "{$key}-{$optionValue}" @endphp
            <div class="form-check form-check-inline">
                {{ html()->radio($key)->value($optionValue)->checked($element['value'] === $optionValue)->id($elementId)->class('form-check-input') }}
                {{ html()->label($optionText)->for($elementId)->class('form-check-label') }}
            </div>
        @endforeach
        </div>
    </div>
    @break
    @case('checkbox')
    <div class="row">
        <div class="col-md-12">
        @foreach($element['options'] as $optionValue => $optionText)
        @php $elementId = "{$key}-{$optionValue}" @endphp
            <div class="form-check form-check-inline">
                {{ html()->checkbox("{$key}[]")->value($optionValue)->checked($element['value'] === $optionValue)->id($elementId)->class('form-check-input') }}
                {{ html()->label($optionText)->for($elementId)->class('form-check-label') }}
            </div>
        @endforeach
        </div>
    </div>
    @break
    @case('switch')
    <div class="form-group col-md-12">
        @foreach($element['options'] as $optionValue => $optionText)
        @php $elementId = "{$key}-{$optionValue}" @endphp
            <div class="custom-control custom-switch">
                {{ html()->checkbox("{$key}[]")->value($optionValue)->checked($element['value'] === $optionValue)->id($elementId)->class('custom-control-input') }}
                {{ html()->label($optionText)->for($elementId)->class('custom-control-label') }}
            </div>
        @endforeach
        </div>
    </div>
    @break
    @case('hidden')
    {{ html()->hidden($key)->value($element['value'])->attributes($element['attr'] ?? []) }}
    @break
    @case('textarea')
    {{ html()->textarea($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('password')
    {{ html()->password($key)->attributes($attributes) }}
    @break
    @case('email')
    {{ html()->email($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('number')
    {{ html()->number($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('price')
        @php $currency = setting('app.currency') @endphp
        @php $attributes['class'] .= ' price' @endphp
        @php $attributes['data-inputmask'] = "'alias': 'numeric', 'groupSeparator': '', 'digits': 2, 'digitsOptional': false, 'prefix': '', 'placeholder': '0'" @endphp
        {{ html()->text($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('date')
        {{ html()->date($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('datepicker')
    @case('datetimepicker')
        @php $attributes['class'] .= " {$element['type']} datetimepicker-input" @endphp
        @php $attributes['data-toggle'] = 'datetimepicker' @endphp
        @php $attributes['data-target'] = "#{$key}" @endphp
        {{ html()->text($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('daterangecustom')
    @case('daterange')
        @php $attributes['class'] .= " {$element['type']} daterange-input" @endphp
        {{ html()->text($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('file')
        <div class="custom-file rounded-0">
            {{ html()->file($key)->attributes($attributes) }}
            {{ html()->label()->for($key)->text($element['label'])->class('custom-file-label') }}
        </div>
        @if($element['value'])
        <div class="card">
            <div class="card-body">
                @if(isset($element['displayAs']))
                    @switch($element['displayAs'])
                        @case('url')
                        {{ file_url($element['value']) }}
                        @break
                        @case('link')
                        <a target="_blank" href="{{ file_url($element['value']) }}"><i class="fas fa-link"></i>&nbsp;Link</a>
                        @break
                        @case('image')
                        <a target="_blank" href="{{ file_url($element['value']) }}"><img src="{{ file_url($element['value']) }}" width="128" height="64" style="object-fit:contain;" /></a>
                        @break
                        @case('download')
                        <a target="_blank" href="{{ download_url($element['value']) }}"><i class="fas fa-download"></i>&nbsp;Download</a>
                        @break
                        @default
                        <em>{{ $element['value'] }}</em>
                    @endswitch
                @else
                <em>{{ $element['value'] }}</em>
                @endif
            </div>
        </div>
        @endif
    @break
    @case('files')
        <div class="custom-file rounded-0">
            {{ html()->file("{$key}[]")->multiple()->id($key)->attributes($attributes) }}
            {{ html()->label()->for($key)->text($element['label'])->class('custom-file-label') }}
        </div>
        @if($element['value'])
        <div class="card">
            <div class="card-body">
                @foreach (explode(',', $element['value']) as $file)
                    @if(isset($element['displayAs']))
                        @switch($element['displayAs'])
                            @case('url')
                            {{ file_url($file) }}
                            @break
                            @case('link')
                            <a target="_blank" href="{{ file_url($file) }}"><i class="fas fa-link"></i>&nbsp;Link</a>
                            @break
                            @case('image')
                            <a target="_blank" href="{{ file_url($file) }}"><img src="{{ file_url($file) }}" width="128" height="64" style="object-fit:contain;" /></a>
                            @break
                            @case('download')
                            <a target="_blank" href="{{ download_url($file) }}"><i class="fas fa-download"></i>&nbsp;Download</a>
                            @break
                            @default
                            <em>{{ $file }}</em>
                        @endswitch
                    @else
                    <em>{{ $file }}</em>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    @break
    @case('url')
    {{ html()->url($key)->value($element['value'])->attributes($attributes) }}
    @break
    @case('text')
    @default
    {{ html()->text($key)->value($element['value'])->attributes($attributes) }}
@endswitch
@if($element['type'] != 'hidden') <span id="{{ $key }}-error" class="error invalid-feedback"></span> @endif