<?php

namespace Samik\LaravelAdmin\Models;

use DB;
use DataTables;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class BaseModel extends Model
{
    use HasFactory;

    /**
     * Label Column
     *
     * This Column will be treated as the de facto Label bearer of the record.
     * If not overriden in child classes, a column 'name' will be assumed
     * by default.
     *
     * @var string $labelColumn
     */
    protected $labelColumn = 'name';
    
    /**
     * Guarded attribute
     *
     * By default, all attributes are considered mass-assignable
     *
     * @var array $guarded
     */
    protected $guarded = [];

    
    /**
     * Filter Attributes
     *
     * If true, when saving, attributes with non-null non-scalar data will be replaced with null
     *
     * @var array $guarded
     */
    protected $filterAttributes = true;

    /**
     * Returns the label value of the current instance of this model.
     *
     * When labelColumn is incorrectly assigned, value of primary key is 
     * returned instead.
     *
     * @return string       the label name
     */
    public function label()
    {
        $label = $this->{$this->labelColumn};
        return $label ?? '#' . $this->{$this->getKeyName()};
    }

    /**
     * Custom accessor for created_at column
     */
    public function getCreatedAtAttribute($value)
    {
        return user_datetime($value);
    }

    /**
     * Custom accessor for updated_at column
     */
    public function getUpdatedAtAttribute($value)
    {
        return user_datetime($value);
    }

    /**
     * Query scope for universal filtering
     * 
     * Query parameters like ?find=abc will be used for wide search (OR WHERE)
     * If &scope=column is provided, finder will find in the specific columns
     * Otherwise it will find on the keys as defined by basemodel's searchable() method
     *
     * Query parameters like ?filter[column] = value will be used for narrow search (AND WHERE)
     * ?filter[column][like] = value will be used for WHERE LIKE
     * ?filter[column][range] = val1 - val2 will be used for WHERE BETWEEN
     * 
     * In every instance, it is possible to describe a relationship in place of a column 
     * by naming the column with a dot noted relationship string such as model.relationship.column
     *
     * @return Illuminate\Database\Eloquent\Builder       the Query scope return
     */
    public function scopeFilter($query)
    {
        $find = \request()->input('find');
        if($find) {
            $fields = static::searchable();
            $scope = \request()->input('scope');
            if($scope) {
                $fields = explode(',', $scope);
            }
            $query->where(function($q1) use ($fields, $find) {
                foreach($fields as $key) {
                    if(Str::contains($key, '.')) {
                        // if key indicates a relationship
                        $relation = (string) Str::of($key)->beforeLast('.');
                        $relationKey = (string) Str::of($key)->afterLast('.');
                        $q1->orWhereHas($relation, function($q2) use ($relationKey, $find) {
                            $q2->where(function($q22) use ($relationKey, $find){
                                foreach(explode(' ', $find) as $keyword) {
                                    $q22->orWhere($relationKey, 'like', "%{$keyword}%");
                                }
                                return $q22;
                            });
                            return $q2;
                        });
                    }
                    else {
                        // if key indicates a column on the table
                        $q1->orWhere(function($q11) use ($key, $find){
                            foreach(explode(' ', $find) as $keyword) {
                                $q11->orWhere($key, 'like', "%{$keyword}%");
                            }
                            return $q11;
                        });
                    }
                }
            });
            $max = \request()->input('max', 10);
            $query->limit($max);
        }

        $filter = \request()->input('filter');
        if($filter) {
            foreach($filter as $key => $val) {
                if($val || $val == 0) {
                    if(Str::contains($key, '.')) {
                        $relation = (string) Str::of($key)->beforeLast('.');
                        $relationKey = (string) Str::of($key)->afterLast('.');
                        $query->whereHas($relation, function($q) use ($relationKey, $val) {
                            return static::applyFilterCondition($q, $relationKey, $val);
                        });
                    }
                    else {
                        $query = static::applyFilterCondition($query, $key, $val);
                    }
                }
            }
        }

        $order = \request()->input('order');
        if($order) {
            $columns = \request()->input('columns');
            foreach($order as $val) {
                $query->orderBy($columns[$val['column']]['name'], $val['dir']);
            }
        }

        $scopes = explode(',', \request()->filled('scopes') ? \request()->input('scopes') : '');
        if($scopes) {
            foreach($scopes as $scope) {
                if(method_exists($this, Str::studly("scope_{$scope}"))) $query->$scope();
            }
        }

        return $query;
    }

    private function filterAttributes()
    {
        if($this->filterAttributes) foreach($this->attributes as $key => &$value) $value = \is_scalar($value) || $value === null ? $value : null;
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->filterAttributes();
        });
    }

    private static function applyFilterCondition($query, $key, $val) 
    {
        if(\is_array($val)) {
            if(isset($val['eq'])) {
                $query->where($key, $val['eq']);
            }
            elseif(isset($val['like'])) {
                $query->where($key, 'like', "%{$val['like']}%");
            }
            elseif(isset($val['range'])) {
                $range = explode(' - ', $val['range']);
                $query->whereBetween($key, $range);
            }
            elseif(isset($val['exists'])) {
                if($val['exists'] === 'no') {
                    $query->doesntHave($key);
                }
                else {
                    $query->has($key);
                }
            }
            else {
                $query->whereIn($key, $val);
            }
        }
        else {
            $query->where($key, $val);
        }
        return $query;
    }

    /**
     * Returns the table name of this model.
     *
     * @return string       the table name
     */
    public static function tableName()
    {
        return with(new static)->getTable();
    }

    /**
     * Returns the primary key column name of this model.
     *
     * @return string       the primary key name
     */
    public static function keyName()
    {
        return with(new static)->getKeyName();
    }

    /**
     * Returns the label column name of this model.
     *
     * @return string       the label column name
     */
    public static function labelName()
    {
        return with(new static)->labelColumn;
    }

    /**
     * Returns the class name of this model.
     *
     * @return string       the classname
     */
    public static function className()
    {
        return class_basename(static::class);
    }

    /**
     * Returns the display name of this model.
     *
     * @return string       the classname
     */
    public static function displayName()
    {
        return title(static::className());
    }

    /**
     * Returns the URI name of this model.
     *
     * @return string       the uri
     */
    public static function uriName()
    {
        return Str::kebab(static::className());
    }

    /**
     * Returns the URI Resource name of this model.
     *
     * @return string       the uri
     */
    public static function resourceName()
    {
        return Str::plural(static::uriName());
    }

    /**
     * Returns the columns of this model as array.
     *
     * @return array        the table columns
     */
    protected static function getAllColumns()
    {
        // return Schema::getColumnListing(static::make()->getTable());
        return Schema::getColumnListing(static::tableName());
    }

    /**
     * Returns the keys (primary and unique) of this model as array.
     *
     * @return array        the table keys
     */
    public static function getTableKeys() 
    {
        $table = static::tableName();

        $keys = [
            'primary' => [],
            'unique' => []
        ];

        //get the array of table indexes
        $indexes =  Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);

        //abort if there are no indexes
        if(!\is_array($indexes)) return false;
        
        foreach($indexes as $index) {
            if($index->isPrimary()) {
                $keys['primary'] = $index->getColumns();
                continue;
            }
            if($index->isUnique() && !$index->isPrimary()) {
                $keys['unique'][] = $index->getColumns()[0];
                continue;
            }
        }

        return $keys;
    }

    /**
     * Defines the elements of this model to be used in advanced search.
     *
     * Returns false by default
     *
     * @return array        the model elements or false
     */
    protected static function filterElements()
    {
        return false;
    }

    /**
     * Defines the elements of this model as array.
     *
     * Elements are generated automatically by default
     * by reading the Schema
     *
     * @return array        the model elements
     */
    protected static function elements()
    {
        $elements = [];
        $columns = static::getAllColumns();
        $tableKeys = static::getTableKeys();
        if(empty($columns) && config('app.env') == 'production') abort(404); // if no columns found in database, show 404 when in production
        foreach($columns as $columnName) {
            if($columnName == static::keyName()) continue;
            if($columnName == 'created_at') continue;
            if($columnName == 'updated_at') continue;
            if($columnName == 'deleted_at') continue;
            $elements[$columnName] = [
                'label' => title($columnName),
                'type' => 'text'
            ];
            if($tableKeys && \in_array($columnName, $tableKeys['unique'])) {
                $elements[$columnName]['unique'] = true;
            }
        }
        return $elements;
    }

    /**
     * Returns the validation rules of this model as array.
     *
     * Validation rules are generated automatically by default
     * based on the elements
     * 
     * @param string $id    optional model PK to alter rules accordingly
     *
     * @return array        the validation rules
     */
    protected static function validationRules($id = null, $customElements = null)
    {
        $rules = [];
        $elements = static::elements();
        $customElements = $customElements ?? static::editable();
        foreach($elements as $key => $element) {
            if($customElements && !\in_array($key, $customElements)) continue;
            $ruleset = [];
            if(isset($element['required']) && $element['required']) {
                $type = $element['type'] ?? 'text';
                if($type != 'password') {
                    $ruleset[] = 'required';
                }
                else {
                    if(!$id) $ruleset[] = 'required';
                }
            }
            else {
                $ruleset[] = 'sometimes';
                $ruleset[] = 'nullable';
            }
            if(isset($element['unique']) && $element['unique']) 
            {
                $rule = 'unique:' . static::tableName();
                if($id) $rule .= ',' . $key . ',' .  $id . ',' . static::keyName();
                $ruleset[] = $rule;
            }
            if(isset($element['type'])) 
            {
                switch($element['type']) {
                    case 'email':
                        $ruleset[] = 'email';
                        break; 
                    case 'number':
                        $ruleset[] = 'numeric';
                        break;
                    case 'password':
                        $ruleset[] = 'nullable';
                        $ruleset[] = 'min:6';
                        break;
                    case 'location':
                        $ruleset[] = 'regex:/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/';
                        break; 
                    case 'radio':
                    case 'select':
                    case 'select2':
                        $options = $element['options'] instanceof \Illuminate\Support\Collection ? ($element['options'])->toArray() : $element['options'];
                        if(empty($options)) break;
                        $validValues = array_keys($options);
                        $ruleset[] = 'in:' . implode(',', $validValues);
                        break;
                    case 'checkbox':
                    case 'multiselect':
                    case 'multiselect2':
                        $options = $element['options'] instanceof \Illuminate\Support\Collection ? ($element['options'])->toArray() : $element['options'];
                        if(empty($options)) break;
                        $validValues = array_keys($options);
                        $ruleset[] = 'array';
                        $ruleset[] = 'in:' . implode(',', $validValues);
                        break;
                }
            }
            if(isset($element['attr']['min'])) 
            {
                $ruleset[] = 'min:' . $element['attr']['min'];
            }
            if(isset($element['attr']['max'])) 
            {
                $ruleset[] = 'max:' . $element['attr']['max'];
            }
            $rules[$key] = $ruleset;
        }
        
        return $rules;
    }
    
    /**
     * Returns the custom validation messages of this model as array.
     *
     * None is provided by default
     * Class must override this method to implement custom messages
     *
     * @return array        the validation messages
     */
    protected static function validationMessages()
    {
        $messages = [];
        
        return $messages;
    }

    /**
     * Validates the incoming request with rules defined by validationRules
     * 
     * @param string $id    optional model PK to alter rules accordingly
     *
     * @return array        the validated input
     */
    protected static function validate($id = null, $customElements = null)
    {
        $validator = Validator::make(\request()->all(), static::validationRules($id, $customElements), static::validationMessages());
        $validator->validate();
        $validated = $validator->validated();
        return $validated;
    }

    /**
     * Defines the array of column names to be displayed in list views
     *
     * @return array        the array of column names
     */
    protected static function listable()
    {
        return array_keys(static::elements());
    }

    /**
     * Defines the array of column names to be displayed in data views
     *
     * @return array        the array of column names
     */
    protected static function viewable()
    {
        return array_keys(static::elements());
    }

    /**
     * Defines the array of column names to be displayed in form views
     *
     * @return array        the array of column names
     */
    protected static function editable()
    {
        $editable = [];
        $elements = static::elements();
        if($elements) {
            foreach ($elements as $key => $element) {
                if($element['editable'] ?? true) {
                    $editable[] = $key;
                }
            }
        }
        return $editable;
    }

    /**
     * Defines the array of column names to be searchaed in queries
     *
     * @return array        the array of column names
     */
    protected static function searchable()
    {
        $searchable = [];
        $elements = static::getAllColumns();
        if($elements) {
            foreach ($elements as $key => $element) {
                if($element['searchable'] ?? false) {
                    $searchable[] = $element['relation'] ?? $key;
                }
            }
        }
        return $searchable;
    }

    /**
     * Defines the array of column names to be displayed in import views
     *
     * @return array        the array of column names
     */
    protected static function importable()
    {
        $importable = [];
        $elements = static::elements();
        if($elements) {
            foreach ($elements as $key => $element) {
                if($element['importable'] ?? true) {
                    $importable[] = $key;
                }
            }
        }
        return $importable;
    }

    /**
     * Defines the array of action buttons to be used in list views
     *
     * @return array        the array of action buttons
     */
    protected static function pageActions()
    {
        $actions = [];
        $className = static::displayName();
        $resourceName = static::resourceName();

        if(\request()->user()->can('create', static::class)) $actions['create'] = [
            'text' => "New {$className}",
            'url' => admin_url("{$resourceName}/new"),
            'class' => 'btn btn-default',
            'iconClass' => 'fas fa-plus-square',
            'modal' => true
        ];

        return $actions;
    }

    /**
     * Defines the array of action buttons to be used in bulk-selected list views (multiple rows)
     *
     * @return array        the array of action buttons
     */
    protected static function bulkActions()
    {
        return [];
    }

    /**
     * Defines the array of action buttons to be used in list views (per row)
     *
     * @return array        the array of action buttons
     */
    protected static function listActions($row)
    {
        $actions = [];
        $uriName = static::resourceName();

        if(\request()->user()->can('read', static::class)) $actions['read'] = [
            'text' => 'View',
            'url' => admin_url("{$uriName}/$1"),
            'class' => 'btn btn-default',
            'iconClass' => 'fas fa-eye',
            'modal' => true
        ];

        if(\request()->user()->can('update', static::class)) $actions['edit'] = [
            'text' => 'Edit',
            'url' => admin_url("{$uriName}/$1/edit"),
            'class' => 'btn btn-warning',
            'iconClass' => 'fas fa-edit',
            'modal' => true
        ];

        if(\request()->user()->can('delete', static::class)) $actions['delete'] = [
            'text' => 'Delete',
            'url' => admin_url("{$uriName}/$1/delete"),
            'class' => 'btn btn-danger',
            'iconClass' => 'fas fa-trash-alt',
            'modal' => true
        ];

        return $actions;
    }

    public static function getQuery()
    {
        $query = static::query()->filter();
        $query->orderByDesc(static::tableName() . '.' . static::keyName());
        return $query;
    }

    public static function getDataTableOptions($source, $config = ['extraParams' => false, 'elements' => false, 'limit' => false, 'toolbar' => true, 'checkboxField' => false])
    {
        $elements = static::elements();
        $displayElements = empty(static::listable()) ? false : static::listable();

        if(isset($config['elements'])) {
            if(is_string($config['elements'])) {
                $displayElements = Str::camel($config['elements']) . "Elements";
            }
            else if(is_array($config['elements'])){
                $displayElements = $config['elements'];
            }
            else {
                // do nothing
            }
        }

        $buttons = [
            [
                'extend' => 'reload',
                'text' => '<i class="fas fa-sync"></i>',
                'titleAttr' => 'Reload'
            ]
        ];


        $pageActions = static::pageActions();
        foreach($pageActions as $action) {
            $buttons[] = [
                'extend' => 'action',
                'text' => '<i class="' . $action['iconClass'] . '"></i>',
                'attr' => [
                    'title' => $action['text'],
                    'data-url' => $action['url']
                ],
            ];
        }
        
        if(static::filterElements()) {

            $buttons[] = [
                'extend' => 'search',
                'text' => '<i class="fas fa-search"></i>',
                'titleAttr' => 'Advanced Search'
            ];
        }

        if(\request()->user()->can('export', static::class)) {

            $buttons[] = [
                'extend' => 'copy',
                'text' => '<i class="fas fa-copy"></i>',
                'titleAttr' => 'Copy',
                'exportOptions' => [
                    'columns' => 'th:not(:last-child)'
                ]
            ];
    
            $buttons[] = [
                'extend' => 'excel',
                'text' => '<i class="fas fa-file-excel"></i>',
                'titleAttr' => 'Export to Excel',
                'exportOptions' => [
                    'columns' => 'th:not(:last-child)'
                ]
            ];
    
            $buttons[] = [
                'extend' => 'pdf',
                'text' => '<i class="fas fa-file-pdf"></i>',
                'titleAttr' => 'Export to PDF',
                'exportOptions' => [
                    'columns' => 'th:not(:last-child)'
                ]
            ];
        }

        if(isset($config['checkboxField']) && is_string($config['checkboxField'])) {
            $bulkActions = static::bulkActions();
            foreach($bulkActions as $action) {
                $buttons[] = [
                    'extend' => 'bulk',
                    'text' => '<i class="' . $action['iconClass'] . '"></i>',
                    'attr' => [
                        'title' => $action['text'],
                        'data-url' => $action['url']
                    ],
                ];
            }
        }

        $options = [
            'ajax' => [
                'url' => $source,
                'dataSrc' => 'data',
            ],
            'columns' => [],
            'order' => [],
            'select' => true,
            'processing' => true,
            'serverSide' => true,
            'stateSave' => true,
            'deferRender' => true,
            'responsive' => true,
            'autoWidth' => true,
            'buttons' => $buttons
        ];
        
        if(isset($config['checkboxField']) && is_string($config['checkboxField'])) {
            $options['columns'][] = ['data' => 'checkbox', 'title' => '<input type="checkbox" id="select-all" class="form-control" />', 'searchable' => false, 'sortable' => false, 'orderable' => false];
            if(isset($config['extraParams']) && is_array($config['extraParams'])) {
                $config['extraParams']['checkboxField'] = $config['checkboxField'];
            }
            else $config['extraParams'] = ['checkboxField' => $config['checkboxField'] ];
        }

        if(isset($config['extraParams']) && is_array($config['extraParams'])) {
            $options['ajax']['data'] = $config['extraParams'];
        }
        
        if(isset($config['toolbar']) && $config['toolbar']) {
            // $options['dom'] = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-md-12't>><'row'<'col-md-12'B>><'row'<'col-md-6'i><'col-md-6'p>>";
            $options['dom'] = "<'row'<'col-md-12'B>><'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-md-12't>><'row'<'col-md-6'i><'col-md-6'p>>";
        }

        if(isset($config['limit']) && is_numeric($config['limit'])) {
            $options['pageLength'] = $config['limit'];
        }

        if($displayElements) {
            // $eagerLoaded = with(new static)->with;
            foreach($displayElements as $elementKey) {
                // $data =  \in_array($elementKey, $eagerLoaded) ? ($elements[$elementKey]['relation'] ?? $elementKey) : $elementKey;
                $data =  Str::snake($elements[$elementKey]['relation'] ?? $elementKey);
                $name =  $elements[$elementKey]['relation'] ?? $elementKey;
                $title =  $elements[$elementKey]['label'] ?? title($elementKey);
                $searchable = $elements[$elementKey]['searchable'] ?? true;
                $sortable = $elements[$elementKey]['sortable'] ?? true;
                $orderable = $elements[$elementKey]['orderable'] ?? true;
                $defaultContent = '<em>Not set</em>';
                $options['columns'][] = ['data' => $data, 'name' => $name, 'title' => $title, 'searchable' => $searchable, 'sortable' => $sortable, 'orderable' => $orderable, 'defaultContent' => $defaultContent];
            }
        }
        
        $options['columns'][] = ['data' => '_action', 'title' => 'Row Action', 'searchable' => false, 'sortable' => false];
        
        return \json_encode($options);
    }

    /**
     * Generates a response object for datatables, building upon the getData result
     *
     * @return object        the datatable response object
     */
    public static function getDataTableResponse(\Illuminate\Database\Eloquent\Builder $query = null)
    {
        $relations = [];
        foreach(static::elements() as $key => $element) {
            if(isset($element['relation'])) {
                $relations[] = substr($element['relation'], 0, strrpos($element['relation'], "."));
            }
        }

        $query = $query ?? static::getQuery();
        if(!empty($relations)) $query->with($relations);

        $datatable = Datatables::of($query)
        ->addIndexColumn()
        ->addColumn('_action', function($row){
            $actionBtn = build_action_html(static::listActions($row), $row->{static::keyName()});
            return $actionBtn;
        });

        $rawColumns = ['_action'];
        foreach(static::elements() as $key => $element) {
            // overwrite values with any datatable accessor present in the model
            $accessor = sprintf('get%sDataTableAttribute', Str::of($key)->studly());
            if(\method_exists(\get_called_class(), $accessor)) $datatable->editColumn($key, fn($row) => $row->{$accessor}());

            // overwrite values for file/files type and options
            if(isset($element['type']) && $element['type'] === 'file' && isset($element['displayAs'])) {
                switch($element['displayAs']) {
                    case 'url': 
                        $datatable->editColumn($key, function($row) use($key) {
                            return file_url($row->{$key});
                        });
                        break;
                    case 'link':
                        $datatable->editColumn($key, function($row) use($key) {
                            $html = '<a target="_blank" href="' . file_url($row->{$key}) . '"><i class="fas fa-link"></i>&nbsp;Link</a>';
                            return $html;
                        });
                        $rawColumns[] = $key;
                        break;
                    case 'image':
                        $datatable->editColumn($key, function($row) use($key) {
                            if($row->{$key}) $html = '<a target="_blank" href="' . file_url($row->{$key}) . '"><img src="' . file_url($row->{$key}) . '" width="128" height="64" style="object-fit:contain;" /></a>';
                            else $html = '<em>Not Set</em>';
                            return $html;
                        });
                        $rawColumns[] = $key;
                        break;
                    case 'download':
                        $datatable->editColumn($key, function($row) use($key) {
                            if($row->{$key}) $html = '<a target="_blank" href="' . download_url($row->{$key}) . '"><i class="fas fa-download"></i>&nbsp;Download</a>';
                            else $html = '<em>Not Set</em>';
                            return $html;
                        });
                        $rawColumns[] = $key;
                        break;
                }
            }
            if(isset($element['type']) && $element['type'] === 'files' && isset($element['displayAs'])) {
                $limit = setting('admin.multifile_preview_limit', 3);
                switch($element['displayAs']) {
                    case 'url': 
                        $datatable->editColumn($key, function($row) use($key, $limit) {
                            $files = \Str::of($row->{$key})->explode(',');
                            $limit = $limit < $files->count() ? $limit : $files->count();
                            $content = $row->{$key} ? $files->take($limit)->map(fn($file) => sprintf('<p>%s</p>', file_url($file)))->join("") : null;
                            return $content ? \Str::of($content)->append(sprintf('<p><em>Showing %d out of %d</em></p>', $limit, $files->count())) : '<em>Not Set</em>';
                        });
                        $rawColumns[] = $key;
                        break;
                    case 'link':
                        $datatable->editColumn($key, function($row) use($key, $limit) {
                            $files = \Str::of($row->{$key})->explode(',');
                            $limit = $limit < $files->count() ? $limit : $files->count();
                            $content = $row->{$key} ? $files->take($limit)->map(fn($file) =>  sprintf('<p><a target="_blank" href="%s"><i class="fas fa-link"></i>&nbsp;Link</a></p>', file_url($file)))->join("") : null;
                            return $content ? \Str::of($content)->append(sprintf('<p><em>Showing %d out of %d</em></p>', $limit, $files->count())) : '<em>Not Set</em>';
                        });
                        $rawColumns[] = $key;
                        break;
                    case 'image':
                        $datatable->editColumn($key, function($row) use($key, $limit) {
                            $files = \Str::of($row->{$key})->explode(',');
                            $limit = $limit < $files->count() ? $limit : $files->count();
                            $content = $row->{$key} ? $files->take($limit)->map(fn($file) =>  sprintf('<a target="_blank" href="%1$s"><img src="%1$s" width="128" height="64" style="object-fit:contain;" /></a>', file_url($file)))->join("") : null;
                            return $content ? \Str::of($content)->prepend('<p>')->append('</p>')->append(sprintf('<p><em>Showing %d out of %d</em></p>', $limit, $files->count())) : '<em>Not Set</em>';
                        });
                        $rawColumns[] = $key;
                        break;
                    case 'download':
                        $datatable->editColumn($key, function($row) use($key, $limit) {
                            $files = \Str::of($row->{$key})->explode(',');
                            $limit = $limit < $files->count() ? $limit : $files->count();
                            $content = $row->{$key} ? $files->take($limit)->map(fn($file) =>  sprintf('<p><a target="_self" href="%s"><i class="fas fa-download"></i>&nbsp;Download</a></p>', download_url($file)))->join("") : null;
                            return $content ? \Str::of($content)->append(sprintf('<p><em>Showing %d out of %d</em></p>', $limit, $files->count())) : '<em>Not Set</em>';
                        });
                        $rawColumns[] = $key;
                        break;
                }
            }
            if(isset($element['options']) && ($element['displayAsOptionValue'] ?? false)) {
                $datatable->editColumn($key, function($row) use($key, $element) {
                    return $element['options'][$row->{$key}] ?? '';
                });
            }
        }
        
        if(\request()->input('checkboxField')) {
            $checkboxField = \request()->input('checkboxField');
            $datatable->addColumn('checkbox', function($row) use($checkboxField) {
                return '<input type="checkbox" name="' . $checkboxField . '[]" value="' . $row->{$checkboxField} .  '" class="form-control select-row" />';
            }, 0);
            $rawColumns[] = 'checkbox';
        }

        $datatable->rawColumns($rawColumns);

        static::modifyDatatable($datatable);

        return $datatable->make(true);
    }

    public static function modifyDatatable($datatable)
    {
        // to be used in inherited class
    }

    public static function getViewData($id)
    {
        $virtuals = [];
        $select = [];
        $with = [];
        foreach(static::elements() as $key => $element) {
            if(\in_array($key, static::viewable())) {
                if($element['relation'] ?? false) {
                    $with[] = Str::of($element['relation'])->beforeLast('.')->toString() . ':id,' . Str::of($element['relation'])->afterLast('.')->toString();
                }
                else {
                    if(\in_array($key, static::getAllColumns())) $select[] = $key;
                    else $virtuals[] = $key;
                }
            }
        }
        $query = static::query();
        // if($with) $query->with($with);
        // if($select) $query->select($select);
        $data = $query->findOrFail($id);
        return $data;
    }

    public static function getFormData($idOrData = [], $createActionUrl = '', $updateActionUrl = '', $customElements = null) 
    {
        $elements = static::elements();
        $displayElements = empty(static::editable()) ? false : static::editable();
        if($customElements) $displayElements = $customElements;
        $formElements = [];

        $id = \is_numeric($idOrData) ? $idOrData : 0;
        $data = \is_array($idOrData) ? $idOrData : ($id ? static::findOrFail($id)->toArray() : []);

        if($displayElements) {
            foreach($displayElements as $elementKey) {
                $element = $elements[$elementKey];
                $element['type'] = $element['type'] ?? 'text';
                if($element['type'] !== 'hidden') $element['label'] = $element['label'] ?? title($elementKey);
                else if($element['label'] ?? false) $element['label'] = $element['label'];
                $element['value'] = $data[$elementKey] ?? ($element['value'] ?? null);
                $element['attr'] = $element['attr'] ?? [];
                switch($element['type']) {
                    case 'select':
                        $element['options'] = $element['options'] ?? [];
                    break;
                }
                $formElements[$elementKey] = $element;
            }
        }

        return [
            'url' => $id ? \str_replace('$1', $id, $updateActionUrl) : $createActionUrl,
            'method' => $id ? 'put' : 'post',
            'elements' => $formElements
        ];
    }

    public static function getImportData($importUrl = '', $verifyUrl = '', $customElements = null) 
    {
        $elements = static::elements();
        $displayElements = empty(static::importable()) ? false : static::importable();
        if($customElements) $displayElements = $customElements;
        $importElements = [];

        if($displayElements) {
            foreach($displayElements as $elementKey) {
                $element = $elements[$elementKey];
                $element['type'] = $element['type'] ?? 'text';
                $element['label'] = $element['label'] ?? title($elementKey);
                $element['attr'] = $element['attr'] ?? [];
                $element['value'] = $element['value'] ?? null;
                switch($element['type']) {
                    case 'select':
                        $element['options'] = $element['options'] ?? [];
                    break;
                }
                $importElements[$elementKey] = $element;
            }
        }

        return [
            'importUrl' => $importUrl,
            'verifyUrl' => $verifyUrl,
            'elements' => $importElements,
        ];
    }

    public static function verifyImport($records) {
        return ['verified' => false, 'records' => []];
    }
}
