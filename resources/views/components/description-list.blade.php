{{-- resources/views/components/description-list.blade.php --}}
@props([
    'items' => [], // Array de ['label' => 'Nombre', 'value' => 'Juan', 'icon' => 'fa-user']
    'horizontal' => false,
    'striped' => false,
    'bordered' => false,
    'compact' => false,
])

@php
    $wrapperClass = '';
    if ($horizontal) {
        $wrapperClass .= ' dl-horizontal';
    }
    if ($striped) {
        $wrapperClass .= ' table-striped';
    }
    if ($bordered) {
        $wrapperClass .= ' table-bordered';
    }
    if ($compact) {
        $wrapperClass .= ' info-compact';
    }
@endphp

@if($horizontal)
    <dl {{ $attributes->merge(['class' => 'row mb-0' . $wrapperClass]) }}>
        @foreach($items as $item)
            @if(!isset($item['hidden']) || !$item['hidden'])
                <dt class="col-sm-4 {{ $compact ? 'py-1' : 'py-2' }}">
                    @if(isset($item['icon']))
                        <i class="fas {{ $item['icon'] }} mr-1 text-muted"></i>
                    @endif
                    {{ $item['label'] }}
                </dt>
                <dd class="col-sm-8 {{ $compact ? 'py-1' : 'py-2' }}">
                    @if(isset($item['badge']))
                        <span class="badge badge-{{ $item['badgeColor'] ?? 'secondary' }}">
                            {{ $item['value'] }}
                        </span>
                    @elseif(isset($item['code']))
                        <code>{{ $item['value'] }}</code>
                    @else
                        {{ $item['value'] ?? '-' }}
                    @endif
                </dd>
            @endif
        @endforeach
    </dl>
@else
    <table {{ $attributes->merge(['class' => 'table mb-0' . $wrapperClass]) }}>
        <tbody>
            @foreach($items as $item)
                @if(!isset($item['hidden']) || !$item['hidden'])
                    <tr>
                        <th class="info-label {{ $compact ? 'py-2' : 'py-3' }}" style="width: 40%;">
                            @if(isset($item['icon']))
                                <i class="fas {{ $item['icon'] }} mr-1 text-muted"></i>
                            @endif
                            {{ $item['label'] }}
                        </th>
                        <td class="{{ $compact ? 'py-2' : 'py-3' }}">
                            @if(isset($item['badge']))
                                <span class="badge badge-{{ $item['badgeColor'] ?? 'secondary' }}">
                                    {{ $item['value'] }}
                                </span>
                            @elseif(isset($item['code']))
                                <code>{{ $item['value'] }}</code>
                            @elseif(isset($item['html']))
                                {{ $item['value'] }}
                            @else
                                {{ $item['value'] ?? '-' }}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif
