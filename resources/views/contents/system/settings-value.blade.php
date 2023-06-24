@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        {{ html()->form('PUT', $apiSetValueUrl)->class('ajax-form')->open() }}
        <div class="card card-primary card-outline">
            <div class="card-header rounded-0">
                <h3 class="card-title">{{ $title }}</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body rounded-0">
                <div class="form-group">
                    {{ html()->label($data->key)->for('setting-value')->class('col-form-label') }}
                    @php
                        $attributes = ['class' => 'form-control rounded-0', 'placeholder' =>  "Set {$data->name}"];
                    @endphp
                    @switch($data->type)
                        @case('select')
                            @php $options = []; foreach(explode(',', $data->options) as $option) $options[$option] = $option; @endphp
                            {{ html()->select('value')->value($data->value)->options($options)->attributes($attributes) }}
                        @break
                        @case('checkbox')
                            <div class="custom-control custom-switch">
                                {{ html()->checkbox("value")->value("1")->checked($data->value == '1')->id('custom-switch')->class('custom-control-input') }}
                                {{ html()->label($data->name)->for('custom-switch')->class('custom-control-label') }}
                            </div>
                        @break
                        @case('daterange')
                            @php $attributes['class'] .= " daterange" @endphp
                            {{ html()->text('value')->value($data->value)->attributes($attributes) }}
                        @break
                        @case('text')
                        @default
                            {{ html()->text('value')->value($data->value)->attributes($attributes) }}
                    @endswitch
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer rounded-0">
                <button type="submit" id="submit-button" class="btn btn-success rounded-0">Submit</button>
                <button type="button" id="default-button" class="btn btn-info rounded-0" data-default="{{ $data->default }}">Default</button>
                <button type="button" id="cancel-button" class="btn btn-default rounded-0 float-right" data-url="{{ $listUrl }}">Cancel</button>
            </div>
        </div>
        {{ html()->form()->close() }}
        <!-- /.card -->
    </div>
    <!--/.col (right) -->
</div>
@endsection
@section('page-scripts')
<script>
    $('#default-button').click(function () {
        let defaultValue = $(this).data('default');
        $('.ajax-form').find('[name=value]').val(defaultValue);
        $('.ajax-form').find('[name=value]').prop('checked', (defaultValue == 1));
    })
</script>
@endsection