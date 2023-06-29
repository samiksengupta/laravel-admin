<?php

namespace Samik\LaravelAdmin\Http\Controllers\Admin;

use Artisan;
use Exception;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Alkhachatryan\LaravelWebConsole\LaravelWebConsole;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Samik\LaravelAdmin\Models\Permission;
use Samik\LaravelAdmin\Models\MenuItem;
use Samik\LaravelAdmin\Http\Controllers\AdminBaseController;

class SystemController extends AdminBaseController
{
    public function doReset()
    {
        if(config('app.env') === 'local') {
            $files = collect(array_diff(scandir('../database/migrations'), ['.', '..']))->map(function($file) {
                return "/database/migrations/{$file}";
            })->values();
            return view('migrations', ['files' => $files]);
        }
        else {
            return "Cannot reset while App is in production";
        }
    }
    
    public function viewConsole(Request $request)
    {
        $this->authorize('console', System::class);
        return LaravelWebConsole::show();
    }

    public function viewCommands(Request $request)
    {
        $this->authorize('commands', System::class);
        $title = 'Command Panel';
        $url = api_admin_url('command');

        //Check if app is not local
        if (!\App::environment('local')) {
            throw new AccessDeniedHttpException();
        }

        // get the full list of artisan commands and store the output
        $commands = $this->getArtisanCommands();

        return view('laravel-admin::contents/system/commands', compact('title', 'url', 'commands'));
    }

    public function apiCommand(Request $request)
    {
        set_time_limit(0);
        $artisanOutput = '';
        if(config('app.env') === 'local') {
            $command = $request->command;
            $args = $request->args;
            $args = (isset($args)) ? ' ' . $args : '';

            try {
                Artisan::call($command . $args);
                $artisanOutput = Artisan::output();
            } catch (Exception $e) {
                $artisanOutput = $e->getMessage();
            }
            return response()->json(compact('artisanOutput'));
        }
        else {
            return response()->json(['artisanOutput' => 'Cannot execute commands while App is in production']);
        }
    }

    private function getArtisanCommands()
    {
        Artisan::call('list');

        // Get the output from the previous command
        $artisanOutput = Artisan::output();
        $artisanOutput = $this->cleanArtisanOutput($artisanOutput);
        $commands = $this->getCommandsFromOutput($artisanOutput);

        return $commands;
    }

    private function cleanArtisanOutput($output)
    {

        // Add each new line to an array item and strip out any empty items
        $output = array_filter(explode("\n", $output));

        // Get the current index of: "Available commands:"
        $index = array_search('Available commands:', $output);

        // Remove all commands that precede "Available commands:", and remove that
        // Element itself -1 for offset zero and -1 for the previous index (equals -2)
        $output = array_slice($output, $index - 2, count($output));

        return $output;
    }

    private function getCommandsFromOutput($output)
    {
        $commands = [];

        foreach ($output as $outputLine) {
            if (empty(trim(substr($outputLine, 0, 2)))) {
                $parts = preg_split('/  +/', trim($outputLine));
                $command = (object) ['name' => trim(@$parts[0]), 'description' => trim(@$parts[1])];
                array_push($commands, $command);
            }
        }

        return $commands;
    }

    public function downloadStoredFile(Request $request, $file)
    {
        return Storage::download("downloads/{$file}");
    }

    public function downloadPublicFile(Request $request, $file)
    {
        return Storage::download("public/{$file}");
    }

    public function viewDashboard(Request $request)
    {
        $this->viewData['title'] = 'Dashboard';

        return view('laravel-admin::contents.system.dashboard', $this->viewData);
    }

    public function viewMenuItems(Request $request)
    {
        $this->authorize('read', MenuItem::class);
        $this->viewData['title'] = 'Menu Items';
        $this->viewData['form'] = [
            'url' => api_admin_url('menu-items'),
            'method' => 'put',
        ];
        $this->viewData['menuItems'] = MenuItem::hierarchy()->get()->toJson();
        $this->viewData['reorderUrl'] = api_admin_url('menu-items');
        $this->viewData['resourceUrl'] = api_admin_url('menu-items');
        $this->viewData['permissionOptions'] = \query_to_options(Permission::query());
        return view('laravel-admin::contents/system/menu-item', $this->viewData);
    }

    public function apiReorderMenuItems(Request $request)
    {
        $this->authorize('reOrder', MenuItem::class);
        MenuItem::reorder($request->get('data'));
        return response()->json(['message' => "Menu was Reordered successfully! Please refresh to see changes.", 'menu' => MenuItem::hierarchy()->get()->toJson()], 200);
    }

    public function apiCreateMenuItem(Request $request)
    {
        $this->authorize('create', MenuItem::class);
        MenuItem::createNew($request->get('data'));
        return response()->json(['message' => "Menu Item was Created successfully! Please refresh to see changes.", 'menu' => MenuItem::hierarchy()->get()->toJson()], 200);
    }

    public function apiUpdateMenuItem(Request $request, $id)
    {
        $this->authorize('update', MenuItem::class);
        MenuItem::updateAt($id, $request->get('data'));
        return response()->json(['message' => "Menu Item was Updated successfully! Please refresh to see changes.", 'menu' => MenuItem::hierarchy()->get()->toJson()], 200);
    }

    public function apiDeleteMenuItem(Request $request, $id)
    {
        $this->authorize('delete', MenuItem::class);
        MenuItem::deleteAt($id);
        return response()->json(['message' => "Menu Item was Deleted successfully! Please refresh to see changes.", 'menu' => MenuItem::hierarchy()->get()->toJson()], 200);
    }
}
