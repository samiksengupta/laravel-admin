@if($filterElements ?? false)
<div class="collapse" id="extraFilters">
    {{ html()->form()->id('filter-form')->open() }}
    <div class="card card-body">
        @php 
        $get = collect(Request::all())->mapWithKeys(function($item, $key) {
            return [str_replace("_", ".", $key) => $item];
        });
        @endphp
        <div class="row">
            @foreach($filterElements as $key => $element)
            @php $type = $element['type'] ?? 'text' @endphp
            @php $label = $element['label'] ?? $element['key'] @endphp
            @php $group = $element['group'] ?? 'where' @endphp
            @php $name = "filter[{$element['key']}]" @endphp
            @php if($group == 'like') $name .= "[like]" @endphp
            @php if($group == 'range') $name .= "[range]" @endphp
            @php if($group == 'scope') $name .= "[scope]" @endphp
            @php if($group == 'exists') $name .= "[exists]" @endphp
            @php $attributes = ['class' => 'form-control rounded-0 extra-filter'] @endphp
            <div class="col-md-4">
                <div class="form-group">
                    {{ html()->label()->for($name)->text($label)->class('col-form-label') }}
                    @switch($type)
                        @case('select')
                        {{ html()->select($name)->value($get[$element['key']] ?? null)->options(collect($element['options'])->prepend('(Any)', ''))->attributes($attributes) }}
                        @break
                        @case('select2')
                        {{ html()->select($name)->value($get[$element['key']] ?? null)->options(collect($element['options'])->prepend('(Any)', ''))->class('form-control rounded-0 extra-filter select2') }}
                        @break
                        @case('daterange')
                        {{ html()->text($name)->value($get[$element['key']] ?? null)->class('form-control rounded-0 extra-filter daterange') }}
                        @break
                        @case('datetimerange')
                        {{ html()->text($name)->value($get[$element['key']] ?? null)->class('form-control rounded-0 extra-filter datetimerange') }}
                        @break
                        @case('text')
                        @default
                        {{ html()->text($name)->value($get[$element['key']] ?? null)->attributes($attributes) }}
                    @endswitch
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="button" class="btn btn-success rounded-0" onClick="setExtraFilters()"><i class="fas fa-search"></i>&nbsp;Search</button>
                <button type="button" class="btn btn-default rounded-0" onClick="clearExtraFilters()"><i class="fas fa-eraser"></i>&nbsp;Clear</button>
            </div>
        </div>
    </div>
    {{ html()->form()->close() }}
</div>
@endif