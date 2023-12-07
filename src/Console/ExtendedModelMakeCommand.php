<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;

class ExtendedModelMakeCommand extends ModelMakeCommand
{
    protected $name = 'make:xmodel';

    protected $description = 'Create a new extended model class';
    
    protected function getStub()
    {
        if ($this->option('pivot')) {
            return $this->resolveStubPath('/stubs/model.pivot.stub');
        }

        if ($this->option('morph-pivot')) {
            return $this->resolveStubPath('/stubs/model.morph-pivot.stub');
        }

        return __DIR__ . '/stubs/model.php.stub';
    }
}