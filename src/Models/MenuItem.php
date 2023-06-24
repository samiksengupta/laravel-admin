<?php

namespace Samik\LaravelAdmin\Models;

use Samik\LaravelAdmin\Models\BaseModel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuItem extends BaseModel
{

    protected $appends = ['url', 'has_children', 'active'];

    public function permission()
    {
        return $this->hasOne(\App\Models\Permission::class, 'id', 'permission_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function getUrlAttribute($value)
    {
        return $this->path ? (filter_var($this->path, FILTER_VALIDATE_URL) ? $this->path : admin_url($this->path)) : '#';
    }

    public function getHasChildrenAttribute($value)
    {
        return count($this->children) > 0;
    }

    public function getActiveAttribute($value)
    {
        if($this->has_children) {
            $subMenuActive = false;
            foreach ($this->children as $item) {
                if($item->active) {
                    $subMenuActive = true;
                    break;
                }
            }
            return ($subMenuActive);
        }
        else {
            return ($this->path == request()->path());
        }
        
    }

    public function scopeVisible($query)
    {
        $query->where('display', '1');
        return $query;
    }

    public function scopeHierarchy($query)
    {
        $query->visible();
        $query->orderBy('order');
        $query->with(['children' => function($q0) {
            $q0->orderBy('order');
            if (!Auth()->user()->role->unrestricted) $q0->whereIn('permission_id', Auth()->user()->role->permissions->pluck('id'));
        }]);
        $query->where(function ($q1) {
            $q1->whereDoesntHave('parent');
            $q1->whereDoesntHave('children');
            if (!Auth()->user()->role->unrestricted) $q1->whereIn('permission_id', Auth()->user()->role->permissions->pluck('id'));
        });
        $query->orWhere(function ($q2) {
            $q2->whereDoesntHave('parent');
            $q2->whereHas('children', function ($q21) {
                if (!Auth()->user()->role->unrestricted) $q21->whereIn('permission_id', Auth()->user()->role->permissions->pluck('id'));
            });
        });
        return $query;
    }

    public static function reorder($data)
    {
        foreach ($data as $item) {
            $menuItem = self::findOrFail($item['id']);
            $menuItem->parent_id = $item['parent_id'] ?? null;
            $menuItem->order = $item['order'];
            $item = $menuItem->save();
        }
    }

    public static function createNew($data)
    {
        $menuItem = new MenuItem();
        $menuItem->parent_id = null;
        $menuItem->text = $data['text'] ?? "Menu Item";
        $menuItem->path = $data['path'] ?? null;
        $menuItem->icon_class = $data['icon_class'] ?? 'fas fa-circle';
        $menuItem->target = $data['target'];
        $menuItem->permission_id = $data['permission_id'] ?? null;
        $menuItem->order = 0;
        $menuItem->display = $data['display'] ?? 1;
        $menuItem->save();
    }

    public static function updateAt($id, $data)
    {
        $menuItem = self::findOrFail($id);
        $menuItem->text = $data['text'] ?? "Menu Item #{$id}";
        $menuItem->path = $data['path'] ?? null;
        $menuItem->icon_class = $data['icon_class'] ?? 'fas fa-circle';
        $menuItem->target = $data['target'];
        $menuItem->permission_id = $data['permission_id'] ?? null;
        $menuItem->display = $data['display'] ?? 1;
        $menuItem->save();
    }

    public static function deleteAt($id)
    {
        $menuItem = self::findOrFail($id);
        self::unOrphan($id);
        $menuItem->delete();
    }

    private static function unOrphan($id)
    {
        self::where('parent_id', $id)->update(['parent_id' => null]);
    }
}
