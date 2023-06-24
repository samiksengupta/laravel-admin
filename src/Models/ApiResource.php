<?php

namespace Samik\LaravelAdmin\Models;

use Samik\LaravelAdmin\Models\BaseModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiResource extends BaseModel
{
    use HasFactory;

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    public static function getAllMethods() 
    {
        return [
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_PATCH,
            self::METHOD_DELETE
        ];
    }

    /**
     * Defines the array of action buttons to be used in list views (per row)
     *
     * @return array        the array of action buttons
     */
    protected static function listActions($row)
    {
        $actions = parent::listActions($row);
        $uriName = static::resourceName();

        if(\request()->user()->can('test', static::class)) $actions['test'] = [
            'text' => 'Test',
            'url' => admin_url("{$uriName}/$1/test"),
            'class' => 'btn btn-default',
            'iconClass' => 'fas fa-screwdriver',
            'modal' => false
        ];

        return $actions;
    }
    
    public function setFieldsAttribute($value)
    {
        $this->attributes['fields'] = preg_replace('/\s+/', '', $value);
    }

    public function getFieldsAttribute()
    {
        return $this->attributes['fields'];
    }

    public function getFields()
    {
        $trimmed = preg_replace('/\s+/', '', $this->attributes['fields']);
        $fields = empty($trimmed) ? [] : \explode(",", $trimmed);
        return $fields;
    }

    public static function elements()
    {
        return [
            'name' => [
                'required' => true
            ],
            'method' => [
                'type' => 'select',
                'options' => \array_to_options(self::getAllMethods()),
                'required' => true
            ],
            'route' => [
                'required' => true
            ],
            'fields' => [],
            'secure' => [
                'type' => 'radio',
                'options' => ['No', 'Yes'],
                'required' => true
            ],
            'hidden' => [
                'type' => 'radio',
                'options' => ['No', 'Yes'],
                'required' => true
            ],
            'disabled' => [
                'type' => 'radio',
                'options' => ['No', 'Yes'],
                'required' => true
            ],
        ];
    }

    public static function listable()
    {
        return ['name', 'method', 'route'];
    }
}
