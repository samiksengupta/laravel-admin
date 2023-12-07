@extends('laravel-admin::layouts.none')
@section('content')
<div class="page-content container-fluid">
    <div id="commands">
        <h3><i class="fas fa-terminal"></i> {{ $title }} <small>Execute artisan commands</small></h3>
        <div id="command_lists" class="col-md-12">
            @foreach($commands as $command)
                <div class="command" data-command="{{ $command->name }}">
                    <code>php artisan {{ $command->name }}</code>
                    <small>{{ $command->description }}</small>
                    <form action="{{ $url }}" class="cmd_form" method="POST">
                        {{ csrf_field() }}
                        <input type="text" name="args" autofocus class="form-control" placeholder="Additional args">
                        <input type="submit" class="btn btn-primary pull-right delete-confirm" value="Run Command">
                        <input type="hidden" name="command" id="hidden_cmd" value="{{ $command->name }}">
                    </form>
                </div>
            @endforeach
        </div>
        <div class="col-md-12 footer navbar-fixed-bottom">
            <pre>
                <i class="close-output">Clear Output</i><span class="art_out">Command Output:</span><span class="art_terminal"></span>
            </pre>
        </div>
    </div>
</div>
@endsection
@section('page-styles')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
<!-- Font Awesome -->
<link rel="stylesheet" href="{{ admin_asset_url('plugins/fontawesome-free/css/all.min.css') }}">
<style type="text/css">

    #command_lists {
        display: flex;
        flex-wrap: wrap;
    }

    #commands h3 {
        width: 100%;
        clear: both;
        margin-bottom: 20px;
    }

    #commands h3 i {
        position: relative;
        top: 3px;
    }

    #commands .command {
        padding: 10px;
        border: 1px solid #f1f1f1;
        border-radius: 4px;
        border-bottom: 2px solid #f5f5f5;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        padding-top: 30px;
        padding-right: 52px;
        flex: 1;
        min-width: 275px;
        margin: 10px;
        margin-left: 0px;
    }

    #commands .command.more_args {
        padding-bottom: 40px;
    }

    #commands .command i {
        position: absolute;
        right: 4px;
        top: -6px;
        font-size: 45px;
    }

    #commands code {
        color: #549DEA;
        padding: 4px 7px;
        font-weight: normal;
        font-size: 12px;
        background: #f3f7ff;
        border: 0px;
        position: absolute;
        top: 0px;
        left: 0px;
        border-bottom-left-radius: 0px;
        border-top-right-radius: 0px;
    }

    #commands .command:hover {
        border-color: #eaeaea;
        border-bottom-width: 2px;
    }

    .cmd_form {
        display: none;
        position: absolute;
        bottom: 0px;
        left: 0px;
        width: 100%;
        margin-block-end: 0em;
    }

    .cmd_form input[type="text"],
    .cmd_form input[type="submit"] {
        width: 30%;
        float: left;
        margin: 0px;
        font-size: 12px;
    }

    .cmd_form input[type="text"] {
        line-height: 30px;
        padding-top: 0px;
        padding-bottom: 0px;
        height: 30px;
        border-top-right-radius: 0px;
        border-bottom-right-radius: 0px;
        border-top-left-radius: 0px;
        padding-left: 5px;
        font-size: 12px;
        width: 70%;
    }

    .cmd_form .form-control.focus,
    .cmd_form .form-control:focus {
        border-color: #eee;
    }

    .cmd_form input[type="submit"] {
        border-top-right-radius: 0px;
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
        font-size: 10px;
        padding-left: 7px;
        padding-right: 7px;
        height: 30px;
    }


    #commands pre {
        display: none;
        background: #323A42;
        color: #fff;
        width: 100%;
        margin: 10px;
        margin-left: 0px;
        padding: 15px;
        padding-top: 0px;
        padding-bottom: 0px;
        position: relative;
        overflow-y: scroll; 
        height: 200px;
    }

    #commands .close-output {
        position: absolute;
        right: 15px;
        top: 15px;
        color: #ccc;
        cursor: pointer;
        padding: 5px 14px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 25px;
        transition: all 0.3s ease;
    }

    #commands .close-output:hover {
        color: #fff;
        background: rgba(0, 0, 0, 0.3);
    }

    #commands pre i:before {
        position: relative;
        top: 3px;
        right: 5px;
    }

    #commands pre .art_out {
        width: 100%;
        display: block;
        color: #98cb00;
        margin-bottom: 10px;
    }
</style>
@endsection
@section('page-scripts')
<!-- jQuery -->
<script src="{{ admin_asset_url('plugins/jquery/jquery.min.js') }}"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
<!-- JS for commands -->
<script>
    $(document).ready(function() {
        $('.command').click(function() {
            $(this).find('.cmd_form').slideDown();
            $(this).addClass('more_args');
            $(this).find('input[type="text"]').focus();
        });

        $('.close-output').click(function() {
            $('#commands pre').slideUp();
        });

        $(".cmd_form").submit(function(e) {

            e.preventDefault(); // avoid to execute the actual submit of the form.

            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(), // serializes the form's elements.
                beforeSend: function() {
                    $('.cmd_form').slideUp();
                    $('#commands pre').slideDown();
                    $('.art_terminal').text("Please wait...");
                },
                success: function(data) {
                    $('.cmd_form').slideUp();
                    $('#commands pre').slideDown();
                    $('.art_terminal').text(data.artisanOutput);
                },
                error: function (xhr) {
                    $('.cmd_form').slideUp();
                    $('#commands pre').slideDown();
                    $('.art_terminal').text(`Code ${xhr.status}: ${xhr.statusText}`);
                }
            });
        });
    });
</script>
@endsection
