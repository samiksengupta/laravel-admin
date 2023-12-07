<?php

namespace Samik\LaravelAdmin\Models;

use Samik\LaravelAdmin\Models\BaseModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends BaseModel
{
    use HasFactory;

    const INPUT_TEXT = 'text';
    const INPUT_SELECT = 'select';
    const INPUT_CHECKBOX = 'checkbox';
    const INPUT_DATERANGE = 'daterange';

    protected $primaryKey = 'key';
    protected $keyType  = 'string';

    public static function elements()
    {
        return [
            'key' => [
                'required' => true,
                'unique' => true,
            ],
            'name' => [
                'label' => 'Name'
            ],
            'value' => [],
            'type' => [
                'required' => true,
                'type' => 'select',
                'options' => [self::INPUT_TEXT => ucfirst(self::INPUT_TEXT), self::INPUT_SELECT => ucfirst(self::INPUT_SELECT), self::INPUT_CHECKBOX => ucfirst(self::INPUT_CHECKBOX), self::INPUT_DATERANGE => ucfirst(self::INPUT_DATERANGE)]
            ],
            'options' => [],
            'default' => []
        ];
    }

    public static function listable()
    {
        return ['key', 'name', 'value'];
    }

    public static function editable()
    {
        return ['key', 'name', 'type', 'options', 'default'];
    }
    
    protected static function listActions($row)
    {
        $actions = [];
        $uriName = static::resourceName();

        if(\request()->user()->can('setValue', static::class)) $actions[] = [
            'text' => 'Set Value',
            'url' => admin_url("{$uriName}/$1/value"),
            'class' => 'btn btn-default',
            'iconClass' => 'fas fa-wrench',
            'modal' => true
        ];

        if(\request()->user()->can('update', static::class)) $actions[] = [
            'text' => 'Edit',
            'url' => admin_url("{$uriName}/$1/edit"),
            'class' => 'btn btn-warning',
            'iconClass' => 'fas fa-edit',
            'modal' => true
        ];

        if(\request()->user()->can('delete', static::class)) $actions[] = [
            'text' => 'Delete',
            'url' => admin_url("{$uriName}/$1/delete"),
            'class' => 'btn btn-danger',
            'iconClass' => 'fas fa-trash-alt',
            'modal' => true
        ];

        return $actions;
    }
}
