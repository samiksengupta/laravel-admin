<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Foundation\Console\PolicyMakeCommand;

class ExtendedPolicyMakeCommand extends PolicyMakeCommand
{
    protected $name = 'make:xpolicy';

    protected $description = 'Create a new extended policy class';
    
    protected function getStub()
    {
        return __DIR__ . '/stubs/policy.php.stub';
    }
}