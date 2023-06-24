@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        <form id="test-api-form">
        <div class="card card-primary card-outline">
            <div class="card-header rounded-0">
                <h3 class="card-title"><strong>Fill in the parameters and click {{ $requestButtonText }}</strong></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body rounded-0">
                <div class="page-content container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="card-text"><strong>API Endpoint</strong></p>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text api-method" id="api-url-addon">{{ $apiResource->method }}</span>
                                        </div>
                                        <input id="api-url" readonly type="text" value="{{ url("api/{$apiResource->route}") }}" data-url-template="{{ url("api/{$apiResource->route}") }}" data-method="{{ $apiResource->method }}" data-refresh-url="{{ url('api/refresh') }}" class="form-control api-url" placeholder="API URL" aria-describedby="api-url-addon">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary copy-btn input-btn" data-clipboard-target="#api-url" type="button">Copy</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    @if(count($pathParameters[0]))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="card-text"><i class="fas fa-exclamation-triangle"></i> This API route needs <strong>path parameters</strong>. Provide these parameters to obtain a meaningful API endpoint.</p>
                                            <div class="col-md-12">
                                                @foreach ($pathParameters[0] as $i => $param)
                                                <div class="form-group">
                                                    <label class="control-label col-sm-3" for="field-{{ $pathParameters[1][$i] }}" >{{ $pathParameters[1][$i] }}</label>
                                                    <div class="col-sm-9">
                                                        <input required id="field-{{ $param }}" type="text" class="form-control path-param" placeholder="{{ $param }}" data-placeholder="{{ $param }}">
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($apiResource->secure)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="card-text"><i class="fas fa-exclamation-triangle"></i> This Request needs a <strong>token</strong> for authentication</p>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label col-sm-3" for="field-token" >token</label>
                                                    <div class="col-sm-9">
                                                        <input name="token" id="field-token" type="text" class="form-control param" placeholder="token">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-12">
                                            @if(count($fields))
                                            <p class="card-text"><i class="fas fa-exclamation-triangle"></i> This Request accepts <strong>{{ count($fields) }} parameters</strong></p>
                                            <div class="col-md-12">
                                                @foreach ($fields as $field)
                                                <div class="form-group">
                                                    <label class="control-label col-sm-3" for="field-{{ $field }}" >{{ $field }}</label>
                                                    <div class="col-sm-9">
                                                        <input name="{{ $field }}" id="field-{{ $field }}" type="text" class="form-control param" placeholder="{{ $field }}">
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <p class="card-text"><i class="voyager-info-circled"></i> This Request accepts <strong>no additional parameters</strong></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <p class="card-text"><i class="fas fa-bolt"></i> JSON Autofill:</p>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <textarea id="json-string" rows="{{ 1 + (3 * $apiResource->secure) + (3 * count($pathParameters[0])) + (3 * count($fields)) }}" class="form-control" placeholder="Put a JSON string here and click Populate to quickly populate the fields"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <button id="json-fill" class="btn btn-info input-btn pull-left" type="button">Populate</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button id="test-api" class="btn btn-success input-btn btn-lg" type="submit">{{ $requestButtonText }}</button>
                        </div>
                    </div>
                    <hr/>
                    <div id="response-box" class="row" style="display: none;">
                        <div class="col-md-6">
                            <div class="card card-primary card-outline">
                                <div class="card-header rounded-0"><h5 class="card-title">Response from Server&nbsp;(<strong><span id="response-status"></span></strong>)</h5></div>
                                <div class="card-body">
                                    <pre id="response-data"><code></code></pre>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-secondary card-outline"">
                                <div class="card-header rounded-0"><h5 class="card-title">Parameters sent using {{ $apiResource->method }}</h5></div>
                                <div class="card-body">
                                    <pre><code id="request-params"></code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer rounded-0">
                <a type="button" id="cancel-button" class="btn btn-default rounded-0 float-right" href="{{ admin_url('api-resources') }}">Cancel</a>
            </div>
        </div>
        </form>
        <!-- /.card -->
    </div>
    <!--/.col (right) -->
</div>
@endsection
@section('page-styles')
    <link href="{{ admin_asset_url('styles/app/api-tester.css') }}" rel="stylesheet" type="text/css" >
@stop
@section('page-scripts')
<script src="{{ admin_asset_url('scripts/clipboard.min.js') }}"></script>
<script src="{{ admin_asset_url('scripts/app/api-tester.js') }}"></script>
@endsection