@extends('laravel-admin::contents.generic.form')
@section('container', $vueId ?? 'vue-app')
@section('page-scripts')
<!-- VueJs -->
<script src="{{ admin_asset_url('scripts/vue.min.js') }}"></script>
@endsection