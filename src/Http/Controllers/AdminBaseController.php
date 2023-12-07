<?php

namespace Samik\LaravelAdmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBaseController extends Controller
{
    protected $viewData = [];

    function __construct()
    {
        $this->viewData = [
            'title' => "",
            'breadcrumbs' => [],
            'loginUrl' => admin_url('login'),
            'homeUrl' => admin_url('/'),
            'createModal' => true,
            'modal' => (\request()->get('view', 'page') === 'modal')
        ];
    }
}
