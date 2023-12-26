@foreach($form['elements'] as $key => $element)
    @if (@$element['group'] || (isset($element['type']) && $element['type'] !== 'hidden'))
    <div id="group_{{ $key }}" class="form-group">
        @include('laravel-admin::partials.form-control-gen', ['key' => $key])
    </div>
    @else
    @include('laravel-admin::partials.form-control-gen', ['key' => $key])
    @endif
@endforeach