<?php

namespace Samik\LaravelAdmin\Models;

use Samik\LaravelAdmin\Models\BaseModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use Samik\LaravelAdmin\Models\Permission;
use Samik\LaravelAdmin\Models\User;

class Role extends BaseModel
{
    use HasFactory;

    protected $hidden = ['unrestricted'];
    protected $labelColumn = 'name';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function setPreferencesAttribute($value)
    {
        $this->attributes['preferences'] = $value ? $value->toJson() : json_encode([]);
    }

    public function getPreferencesAttribute($value)
    {
        return collect(json_decode($value));
    }

    public function scopeVisible($query)
    {
        return $query->where('level', '>=', (auth_user()->role->level ?? 0));
    }

    public static function elements()
    {
        return [
            'name' => [
                'required' => true,
            ],
            'level' => [
                'type' => 'number',
                'attr' => ['min' => (auth_user()->role->level ?? 0)],
                'value' => (auth_user()->role->level ?? 0) + 1,
                'required' => true,
            ],
        ];
    }

    public static function listable()
    {
        return ['name', 'level'];
    }

    public static function editable()
    {
        return ['name', 'level'];
    }

    protected static function listActions($row)
    {
        $actions = parent::listActions($row);
        $uriName = static::resourceName();

        if(\request()->user()->can('setPermissions', static::class)) $actions[] = [
            'text' => 'Set Permissions',
            'url' => admin_url("{$uriName}/$1/permissions"),
            'class' => 'btn btn-default',
            'iconClass' => 'fas fa-toggle-on',
            'modal' => true
        ];

        if(\request()->user()->can('setPermissions', static::class)) $actions[] = [
            'text' => 'Set Permissions (Switch View)',
            'url' => admin_url("{$uriName}/$1/permission/switches"),
            'class' => 'btn btn-default',
            'iconClass' => 'fas fa-toggle-on',
            'modal' => false
        ];

        return $actions;
    }

    public static function getQuery()
    {
        $query = parent::getQuery();
        $query->visible();
        return $query;
    }
}
