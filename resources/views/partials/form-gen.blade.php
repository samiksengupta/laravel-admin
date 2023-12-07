@foreach($form['elements'] as $key => $element)
    <div id="group_{{ $key }}" class="form-group">
    @include('laravel-admin::partials.form-control-gen', ['key' => $key])
    </div>
@endforeach