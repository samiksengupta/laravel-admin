<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Routing\Console\ControllerMakeCommand;

class ExtendedControllerMakeCommand extends ControllerMakeCommand
{
    protected $name = 'make:xcontroller';

    protected $description = 'Create a new extended controller class';

    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.php.stub';
    }
}