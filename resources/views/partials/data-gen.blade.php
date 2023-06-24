
<table class="table table-hover text-nowrap">
    <tbody>
        @foreach($data->toArray() as $key => $value)
        <tr>
            <th class="col-md-3">{{ title($key) }}</th>
            <td class="col-md-9">
                @if (\is_array($value))
                <table class="table table-bordered">    
                    <tbody>
                    @foreach($value as $valueKey => $valueVal)
                        @if (\is_string($valueVal))
                        <tr>
                            <th class="col-md-3">{{ title($valueKey) }}</th>
                            <td class="col-md-9">{{ $valueVal }}</td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                @else
                    @php $element = $model::elements()[$key] ?? null @endphp
                    @if ($element && ($element['type'] ?? false) && \in_array($element['type'], ['file', 'files', 'url']))
                        @switch ($element['type'])
                            @case('url')
                                @if (!empty($value))
                                <a target="_blank" href="{{ $value }}">{{ $value }}</a>
                                @endif
                            @break
                            @case('file')
                                @if(isset($element['displayAs']))
                                    @switch($element['displayAs'])
                                        @case('url')
                                        {{ file_url($value) }}
                                        @break
                                        @case('link')
                                        <a target="_blank" href="{{ file_url($value) }}"><i class="fas fa-link"></i>&nbsp;Link</a>
                                        @break
                                        @case('image')
                                        <a target="_blank" href="{{ file_url($value) }}"><img src="{{ file_url($value) }}" width="128" height="64" style="object-fit:contain;" /></a>
                                        @break
                                        @case('download')
                                        <a target="_blank" href="{{ download_url($value) }}"><i class="fas fa-download"></i>&nbsp;Download</a>
                                        @break
                                        @default
                                        <em>{{ $value }}</em>
                                    @endswitch
                                @else
                                <em>{{ $value }}</em>
                                @endif
                            @break
                            @case('files')
                                @foreach (explode(',', $value) as $file)
                                    @if(isset($element['displayAs']))
                                        @switch($element['displayAs'])
                                            @case('url')
                                            {{ file_url($file) }}
                                            @break
                                            @case('link')
                                            <a target="_blank" href="{{ file_url($file) }}"><i class="fas fa-link"></i>&nbsp;Link</a>
                                            @break
                                            @case('image')
                                            <a target="_blank" href="{{ file_url($file) }}"><img src="{{ file_url($file) }}" width="128" height="64" style="object-fit:contain;" /></a>
                                            @break
                                            @case('download')
                                            <a target="_blank" href="{{ download_url($file) }}"><i class="fas fa-download"></i>&nbsp;Download</a>
                                            @break
                                            @default
                                            <em>{{ $file }}</em>
                                        @endswitch
                                    @else
                                    <em>{{ $file }}</em>
                                    @endif
                                    {{-- @php dump($value, File::exists(Storage::path($file))) @endphp
                                    @if (!empty($file) && File::exists(Storage::path($file)))
                                    <p><a target="_blank" href="{{ download_url($value) }}"><i class="fas fa-download"></i>&nbsp;Download</a></p>
                                    @endif --}}
                                @endforeach
                            @break
                            @default
                            {{ $value }}
                        @endswitch
                    @else
                    {{ $value }}
                    @endif
                @endif
            </td>
            
        </tr>
        @endforeach
    </tbody>
</table>