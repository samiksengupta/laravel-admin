@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            {{ html()->form('GET', $formUrl)->class('ajax-download-form')->open() }}
            <div class="card card-info card-outline">
                <div class="card-header rounded-0">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body rounded-0">
                    @hasSection('main-content')
                        @yield('main-content')
                    @else
                    <div id="group_daterange" class="form-group">
                        {{ html()->label()->for('daterange')->text('Date Range')->class('col-form-label') }}
                        {{ html()->text('daterange')->value(\Carbon\Carbon::now()->subMonths($dateRangeMonths ?? 3)->format('Y-m-d') . ' - ' . \Carbon\Carbon::now()->format('Y-m-d'))->class('form-control rounded-0 daterangecustom daterange-input')->required(true)->readonly(true) }}
                    </div>
                    @endif
                </div>
                <!-- /.card-body -->
                <div class="card-footer rounded-0">
                    <button type="submit" class="btn btn-success rounded-0">Prepare to Download</button>
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

    initJqueryDownloadForm();
    initDateRangeCustom();

    function initJqueryDownloadForm(targetForm = '.ajax-download-form', successCallback = null, errorCallback = null, cancelCallback = null) {
        var form = $(targetForm);
        var options = {
            dataType: 'binary',
            xhrFields: {
                'responseType': 'blob'
            },
            success: function (response, status, request) {
                var link = document.createElement('a'),
                filename = request.getResponseHeader('content-disposition').split('filename=')[1].split(';')[0];
                link.href = URL.createObjectURL(response);
                link.download = filename;
                link.click();
                link.remove();
            },
            error: function (jqXHR) {
                let response = jqXHR.responseJSON;
                toastError(`Your request failed. Server responded with code: ${jqXHR.status}`);
            }
        };

        // bind form using 'ajaxForm' 
        form.ajaxForm(options);
    }

    function initDateRangeCustom() {
        let options = {
            autoUpdateInput: false,
            maxSpan: {
                months: {{ $dateRangeMonths ?? 3 }}
            },
            locale: {
                format: dateFormat
            },
            // isInvalidDate: isInvalidDate
        };

        $('.daterangecustom').daterangepicker(options, function(start, end) {
            $('.daterangecustom').val(`${start.format(dateFormat)} - ${end.format(dateFormat)}`);
        });
    }
    </script>
</script>
@endsection