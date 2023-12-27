<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

if (!function_exists('p')) {
    // prints anything and/or die
    function p($stuff = null, bool $die = false, bool $dump = false, bool $pre_wrap = true)
    {
        return pp("DEBUG", $stuff, $die, $dump, $pre_wrap);
    }
}

if (!function_exists('pp')) {
    // prints anything with a label and/or die
    function pp(String $label = "DEBUG", $stuff = null, bool $die = false, bool $dump = false, bool $pre_wrap = true)
    {
        if ($pre_wrap) {
            echo '<pre class="sf-dump">';
            echo "<b>{$label}:</b><br />";
        }
        if ($dump || !$stuff) {
            dump($stuff);
        } else {
            print_r($stuff);
        }

        if ($pre_wrap) {
            echo '</pre>';
        }
        if ($die) {
            die;
        }

        return $stuff;
    }
}

if (!function_exists('q')) {
    // print last or all queries logged with a label
    function q(Bool $all = false)
    {
        $response = "";
        $label = "Last Query";
        $logs = DB::getQueryLog();
        if ($all) {
            $label = "All Queries";
            foreach ($logs as $log) {
                $response .= sql_compose($log['query'], $log['bindings']) . PHP_EOL . PHP_EOL;
            }
        } else {
            $log = end($logs);
            if($log) $response = sql_compose($log['query'], $log['bindings']);
            else $response = "";
        }
        return pp($label, $response);
    }
}

if (!function_exists('qqx')) {
    // print a QueryExecuted object with a label
    function qqx(?string $label, Illuminate\Database\Events\QueryExecuted $exec)
    {
        $label = $label ?? "QUERY";
        return pp($label, sql_compose($exec->sql, $exec->bindings));
    }
}

if (!function_exists('d')) {
    // print a QueryExecuted object with a label
    function d($stuff)
    {
        return dump($stuff);
    }
}

if (!function_exists('sql')) {
    // print a QueryExecuted object with a label
    function sql($query)
    {
        $q = str_replace(array('?'), array('\'%s\''), $query->toSql());
        return vsprintf($q, $query->getBindings());
    }
}

if (!function_exists('handle')) {
    // custom exception handling behavior
    function handle(Exception $e)
    {
        Log::error($e);
        if (config('app.env') == 'production') {
            abort(404);
        }
        else throw $e;
    }
}

if (!function_exists('sql_compose')) {
    // Composes a query using sql string format and bindings
    function sql_compose(String $sql, array $bindings)
    {
        $addSlashes = str_replace('?', "'?'", $sql);
        return vsprintf(str_replace('?', '%s', $addSlashes), $bindings);
    }
}

if (!function_exists('auth_user')) {
    function auth_user()
    {
        try {
            return \Auth::user() ?? auth('api')->user() ?? null;
        }
        catch(Exception $e) {
            return null;
        }
    }
}

if (!function_exists('admin_url')) {
    // Gets the base url for admin
    function admin_url($url = '/')
    {
        $adminPrefix = config('laravel-admin.admin_prefix');
        $adminPrefix = $adminPrefix ? "{$adminPrefix}/" : "";
        return url("{$adminPrefix}{$url}");
    }
}

if (!function_exists('api_admin_url')) {
    // Gets the base url for admin apis
    function api_admin_url($url = '/')
    {
        return url("api/admin/{$url}");
    }
}

if (!function_exists('admin_asset_url')) {
    // Gets the base url for asset files
    function admin_asset_url($path = null)
    {
        return URL::asset('laravel-admin' . ($path ? "/{$path}" : '/'));
        // return URL::asset(config('laravel-admin.admin_prefix') . ($path ? "/{$path}" : '/'));
    }
}

if (!function_exists('admin_view')) {
    // Gets the admin view if it exists with a fallback
    function admin_view($path, $viewData = null)
    {
        return \view()->exists(config('laravel-admin.admin_prefix') . ($path ? "/{$path}" : '/')) ? view(config('laravel-admin.admin_prefix') . ($path ? "/{$path}" : '/'), $viewData) : view(config('laravel-admin.admin_prefix') . ($path ? "/{$path}" : '/'), $viewData);
    }
}

if (!function_exists('format_phone_number')) {
    // strips a phone number of unwanted characters and spaces and add a calling code
    function format_phone_number($number, $callingCode = "+91")
    {
        $formatted = preg_replace('/\s+/', '', $number);
        $formatted = str_replace(['-', '_', '(', ')'], '', $formatted);
        $formatted = preg_replace("/^(?:\+?{$callingCode}|0)?/", $callingCode, $number);
        // $formatted = preg_replace("/^(?:\+?\d{1,3}|0)?/", $callingCode, $number);        // for matching upto 3 digit calling codes

        return $formatted;
    }
}

if (!function_exists('get_cached_response')) {
    // caches response from a http get request and returns cached value if identical request is made
    // if api.cache_response value is not set, caching is skipped entirely and all existing cache files are deleted
    function get_cached_response($url, $values)
    {
        $encoded = \json_encode($values);
        $requestSignature = md5("{$url}:{$encoded}");
        $cacheFile = "{$requestSignature}.json";
        $contents = "";
        $cacheResponse = setting('api.cache_response') ?? false;
        $isCachedResponse = false;

        if ($cacheResponse && Storage::disk('cache')->exists($cacheFile)) {
            $contents = json_decode(Storage::disk('cache')->get($cacheFile));
            $isCachedResponse = true;
        } else {
            $response = Http::get($url, $values);
            $contents = json_objectify($response->json());
            if ($cacheResponse) {
                $encoded = \json_encode($contents);
                Storage::disk('cache')->put($cacheFile, $encoded);
            } else {
                $files = Storage::disk('cache')->allFiles();
                Storage::disk('cache')->delete($files);
            }
        }

        return $contents;
    }
}

if (!function_exists('get_relative_day')) {
    // returns a string describing the relativity between a target timestamp and a main timestamp
    // main timestamp defaults to current date/time if not provided
    function get_relative_day($tsMatch, $tsMain = false, $timezone = 'UTC')
    {
        $value = "";

        // This object represents current or main date/time
        $mainDate = $tsMain ? new DateTime("@$tsMain", new DateTimeZone($timezone)) : new DateTime("now", new DateTimeZone($timezone));
        $mainDate->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $matchDate = new DateTime("@$tsMatch", new DateTimeZone($timezone));
        $matchDate->setTime(0, 0, 0); // reset time part, to prevent partial comparison

        $diff = $mainDate->diff($matchDate);
        $diffDays = (integer) $diff->format("%R%a"); // Extract days count in interval

        switch ($diffDays) {
            case 0:
                $value = "Today";
                break;
            case -1:
                $value = "Yesterday";
                break;
            case +1:
                $value = "Tomorrow";
                break;
            default:
                $value = $matchDate->format('l');
        }

        return $value;
    }
}

if (!function_exists('round_number')) {
    // formats a numer and rounds to $decimalPlaces
    function round_number($number, $decimalPlaces = 2)
    {
        return (float) number_format((float) $number, $decimalPlaces, '.', '');
    }
}

if (!function_exists('format_price')) {
    // formats a numer and rounds to $decimalPlaces
    function format_price($number, $prepend_currency = FALSE) 
    {
		return number_format((float) $number, 2, '.', '');
	}
}

if (!function_exists('to_smallest_currency')) {
    // converts a numeric price to int as the smallest unit of currency
    function to_smallest_currency(mixed $value)
    {
        if($value && is_numeric($value)) return (int) $value * 100;
        else return 0;
    }
}

if (!function_exists('from_smallest_currency')) {
    // converts a smallest unit of currency to it's basic form
    function from_smallest_currency(mixed $value)
    {
        if($value && is_numeric($value)) return (float) format_price($value / 100);
        else return 0;
    }
}

if (!function_exists('financial_year')) {
    // Gets the current financial year
    // if fy.startmonth is not set, start month will default to April
    function financial_year($format = 'Y')
    {
        $currentMonthNumber = intval(date('n'));
        $startMonthNumber = intval(setting('fy.startmonth') ?? 4);
		if ($currentMonthNumber < $startMonthNumber) {
			$year = date($format);
		}
		else {
			$year = date($format) + 1;
		}
		return $year;
    }
}

if (!function_exists('app_now')) {
    // returns a carbon instance based on app timezone
    function app_now()
    {
        return app_time(now());
    }
}

if (!function_exists('db_now')) {
    // returns a carbon instance based on db timezone
    function db_now()
    {
        return db_time(now());
    }
}

if (!function_exists('app_time')) {
    // converts a carbon instance to app timezone
    function app_time(Carbon|string|null $time)
    {
        if(!$time) return $time;
        $time = is_string($time) ? Carbon::parse($time) : $time;
        return $time->setTimezone(setting('app.timezone', 'UTC'));
    }
}

if (!function_exists('db_time')) {
    // converts a carbon instance to db timezone
    function db_time(Carbon|string|null $time)
    {
        if(!$time) return $time;
        $time = is_string($time) ? Carbon::parse($time) : $time;
        return $time->setTimezone(setting('db.timezone', 'UTC'));
    }
}

if (!function_exists('user_time_zone')) {
    // Gets the user's timezone
    function user_time_zone()
    {
        return auth_user()->timezone ?? setting('app.timezone');
    }
}

if (!function_exists('db_timezone')) {
    // Gets the database timezone
    function db_timezone()
    {
        return config('app.timezone') ?? setting('app.timezone');
    }
}

if (!function_exists('user_datetime_format')) {
    // Gets the user's timezone
    function user_datetime_format()
    {
        return auth_user()->datetime_format ?? setting('app.datetimeformat');
    }
}

if (!function_exists('user_datetime')) {
    // Converts a timestamp to the user's native timezone
    function user_datetime($value = false, $toFormat = null)
    {
        $toFormat = $toFormat ?? user_datetime_format();
        $fromTimezone = db_timezone();
        $toTimezone = user_time_zone();
        return Carbon::parse("{$value} {$fromTimezone}")->tz($toTimezone)->format($toFormat);
    }
}

if (!function_exists('timezone_convert')) {
    // converts a unix timestamp from one timezone to another format
    function timezone_convert(?string $format, $timestamp, string $to, ?string $from)
    {
        $format = $format ?? 'U';
        $from = $from ?? 'UTC';
        $date = new DateTime("@$timestamp", new DateTimeZone($from));
        $date->setTimezone(new DateTimeZone($to));
        $converted = $date->format($format);
        return $converted;
    }
}

if (!function_exists('dateformat_convert')) {
    // converts a datetime string into a specific format
    function dateformat_convert($dateString, $to = 'Y-m-d H:i:s A', $from = 'c')
    {
        $dateTime = DateTime::createFromFormat($from, $dateString);
        return $dateTime ? $dateTime->format($to) : $dateString;
    }
}

if (!function_exists('currency_symbol')) {
    // returns currency symbol from currency
    function currency_symbol($code, $fallback = "?")
    {
        $currencyModel = get_model('Currency');
        if($currencyModel) {
            $currency = $currencyModel::where('code', $code)->first();
            return $currency ? $currency->symbol : $fallback;
        }
        return $fallback;
    }
}

if (!function_exists('money_format')) {
    // prints a value as money
    function money_format($value, $currency = null, $append = 'pre')
    {
        $symbol = "";
        
        if($currency) {
            // if a currency has been provided, use it
            $symbol = currency_symbol($currency);
        }
        else {
            // otherwise check if session exists
            if(auth_user()) {
                // if session exists, get currency from user preferences
                $symbol = currency_symbol(auth_user()->currency);
            }
            else {
                // otherwise get default currency from settings
                $symbol = currency_symbol(setting('app.currency'));
            }
        }
        $value = number_format($value, 2, '.', '');
        $formatted = "{$value}";
        switch($append) {
            case 'pre':
                $formatted = "{$symbol}{$formatted}";
            break;
            case 'post':
                $formatted = "{$formatted}{$symbol}";
            break;
        }
        return $formatted;
    }
}

if (!function_exists('add_interval')) {
    // adds certain interval to a carbon instance and returns
    function add_interval($interval, Carbon|string $startTime = null)
    {
        $startTime ??= app_now();
        $startTime = is_string($startTime) ? app_time($startTime) : $startTime;
        switch($interval) {
            case 'Daily': return $startTime->addDay()->startOfDay();
            case 'Weekly': return $startTime->addWeek()->startOfWeek();
            case 'Monthly': return $startTime->addMonth()->startOfMonth();
        }
        return $startTime;
    }
}

if (!function_exists('is_json')) {
    // checks if a string is json
    function is_json(?string $string)
    {
        if($string) {
            json_decode($string);
            return json_last_error() === JSON_ERROR_NONE;
        }
        return false;
    }
}

if (!function_exists('json_objectify')) {
    // converts an array to object by encoding and decoding as json
    function json_objectify($array)
    {
        return json_decode(json_encode($array));
    }
}

if (!function_exists('presentable')) {
    // Replaces _ with spaces and converts it to Title case
    function presentable($string)
    {
        $string = Str::of($string)->replace('_', ' ')->title();
        return (string)$string;
    }
}

if (!function_exists('title')) {
    // Converts a string to Title case (with spaces) after converting it to snake case
    function title($string)
    {
        $string = presentable(Str::of($string)->snake());
        return $string;
    }
}

if (!function_exists('clean_text')) {
    // Removes all tags and special character from text
    function clean_text($string)
    {
        $string = trim(html_entity_decode(strip_tags($string)), " \t\n\r\0\x0B\xC2\xA0");
        return $string;
    }
}

if (!function_exists('file_url')) {
    // Tries to return url to file
    function file_url($path)
    {
        return url(Storage::url($path));
    }
}

if (!function_exists('download_url')) {
    // Tries to return a download link to file
    function download_url($path)
    {
        return admin_url("download/public/{$path}");
    }
}

if (!function_exists('image_url')) {
    // Tries to return an image URL from storage or placeholder if present
    function image_url($path)
    {
        $url = empty($path) ? file_url(setting('admin.placeholder_image')) : file_url($path);
        return $url;
    }
}

if (!function_exists('is_valid_base64_image')) {
    // Validates a string as base64 encoded image
    function is_valid_base64_image($encoded)
    {
        try {
            file_get_contents($encoded);
            return true;
        }
        catch(Exception $ex) {
            return false;
        }
    }
}

if (!function_exists('setting')) {
    // Gettings settings value
    function setting($key, $fallback = null)
    {
        $settingModel = get_model('Setting');
        if($settingModel) {
            return $settingModel::where('key', $key)->first()->value ?? $fallback;
        }
        else return $fallback;
    }
}

if (!function_exists('is_policy_authorized')) {
    // Checks if logged in user or given role id has privilege, using a policy class reference and a policy function name
    function is_policy_authorized($classReference, $functionName, $roleId = null)
    {
        $action = Str::studly(str_replace('Policy', '', \class_basename($classReference))) . '.' . Str::camel($functionName);
        return is_action_authorized($action, $roleId);
    }
}

if (!function_exists('is_action_authorized')) {
    // Checks if logged in user or given role id has privilege, using an action string
    function is_action_authorized($action, $roleId = null)
    {
        try {
            $role = $roleId && get_model('Role') ? get_model('Role')::findOrFail($roleId) : auth_user()->role;
            if($role->unrestricted) return true;
            $authorized = $role->permissions()->where('action', $action)->exists();
            return $authorized;
        }
        catch(Exception $e) {
            return false;
        }
    }
}

if (!function_exists('query_to_options')) {
    // Make key value paired options array from a query builder instance
    function query_to_options(Illuminate\Database\Eloquent\Builder $builder, $keyColumn = null, $valueColumn = null, $prepend = false)
    {
        $model = $builder->getModel();
        $keyColumn = $keyColumn ?? $model::keyName();
        $valueColumn = $valueColumn ?? $model::labelName();
        $collection = $builder->pluck($valueColumn, $keyColumn);
        $collection = prepend_to_options($collection, $prepend);
        return $collection;
    }
}

if (!function_exists('array_to_options')) {
    // Make key value paired options array from a simple array
    function array_to_options($array, $formatDisplay = true, $prepend = false)
    {
        $collection = collect($array)->mapWithKeys(function ($item, $itemKey) use($formatDisplay) {
            $key = is_string($item) ? $item : $item['key'] ?? $itemKey;
            $display = is_string($item) ? $item : $item['display'] ?? $item['key'] ?? $itemKey;
            $display = $formatDisplay ? presentable($display) : $display;
            return [$key => $display];
        });
        $collection = prepend_to_options($collection, $prepend);
        return $collection;
    }
}

if (!function_exists('prepend_to_options')) {
    function prepend_to_options($collection, $prepend = false)
    {
        if($prepend) {
            if($collection instanceof Collection || is_array($collection)) {
                $collection = is_array($collection) ? collect($collection) : $collection;
                if(is_string($prepend)) $collection->prepend($prepend, '');
                else if(is_array($prepend)) foreach(array_reverse($prepend) as $key => $display) $collection->prepend($display, $key);
                else $collection->prepend('Select...', '');
            }
        }
        return $collection;
    }
}

if (!function_exists('build_action_html')) {
    // builds action for listview
    function build_action_html($actions, $var = null, $actionButtonText = 'Action')
    {
        $html = '';
        if($actions && is_array($actions)) {
            $html .= '<div class="btn-group rounded-0">';
            $html .= '<button type="button" class="btn btn-secondary rounded-0 dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">' . $actionButtonText . '&nbsp;&nbsp;<span class="sr-only">Toggle Dropdown</span></button>';
            $html .= '<div class="dropdown-menu rounded-0" role="menu" style>';
            foreach($actions as $item) {
                $item['modal'] = setting('app.modal') ? $item['modal'] ?? false : false;
                if($item['modal']) {
                    $url = $item['url'] ?? '';
                    $title = $item['title'] ?? $item['text'];
                    $htmlFormat = '<a class="dropdown-item nav-link" data-toggle="modal" data-target="#modal" data-backdrop="static" data-remote="%s" data-title="%s">';
                    $html .= \sprintf($htmlFormat, $url, $title);
                }
                else {
                    $target = $item['target'] ?? '_self';
                    $html .= '<a class="dropdown-item" href="' . $item['url'] . '" target="' . $target . '">';
                }
                $html .= '<i class="' . ($item['iconClass'] ?? '') . '"></i>&nbsp;&nbsp;';
                $html .= $item['text'] ?? '';
                $html .= '</a>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        if($var) $html = \str_replace('$1', htmlspecialchars($var), $html);
        return $html;
    }
}

if(!function_exists('action_exists')) {
    function action_exists($action) {
        try {
            action($action);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}

if(!function_exists('get_model')) {
    function get_model($modelName) {
        $namespaces = [
            "App\\Models",
            config('laravel-admin.model_namespace')
        ];
        foreach($namespaces as $namespace) if($model = class_exists("{$namespace}\\{$modelName}") ? "{$namespace}\\{$modelName}" : null) return $model;
        return null;
    }
}

if(!function_exists('get_admin_action_callable')) {
    function get_admin_action_callable($controller, $method) {
        $namespaces = [
            "App\\Http\\Controllers\\Admin",
            config('laravel-admin.admin_controller_namespace')
        ];
        foreach($namespaces as $namespace) if($callable = class_exists("{$namespace}\\{$controller}") ? "{$namespace}\\{$controller}@{$method}" : null) return $callable;
        return null;
    }
}

if(!function_exists('get_api_action_callable')) {
    function get_api_action_callable($controller, $method) {
        $namespaces = [
            "App\\Http\\Controllers\\Api",
            config('laravel-admin.api_controller_namespace')
        ];
        foreach($namespaces as $namespace) if($callable = class_exists("{$namespace}\\{$controller}") ? "{$namespace}\\{$controller}@{$method}" : null) return $callable;
        return null;
    }
}

if (!function_exists('flat_ancestors')) {
    function flat_ancestors($model, $nestedProperty = 'parent')
    {
        $result = [];
        if ($model->{$nestedProperty}) {
            $result[] = $model->{$nestedProperty};
            $result = array_merge($result, flat_ancestors($model->{$nestedProperty}, $nestedProperty));
        }
        return $result;
    }
}

if (!function_exists('flat_descendants')) {
    function flat_descendants($model, $nestedProperty = 'children')
    {
        $result = [];
        foreach ($model->{$nestedProperty} as $child) {
            $result[] = $child;
            if ($child->{$nestedProperty}) {
                $result = array_merge($result, flat_descendants($child, $nestedProperty));
            }
        }
        return $result;
    }
}

if (!function_exists('random_code')) {
    function random_code(int $length = 8, string $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTYZ')
    {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}



