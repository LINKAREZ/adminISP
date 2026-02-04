{{-- resources/views/components/action-buttons.blade.php --}}
@props([
    'showRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'showParams' => [],
    'editParams' => [],
    'deleteParams' => [],
    'showPermission' => null,
    'editPermission' => null,
    'deletePermission' => null,
    'size' => 'sm', // sm, md, lg
    'layout' => 'inline', // inline, dropdown
    'confirmDelete' => true,
    'deleteMessage' => '¿Está seguro de eliminar este registro?',
    'customActions' => [], // Array de acciones personalizadas
])

@php
    $btnClass = 'btn btn-' . ($size === 'sm' ? 'sm' : ($size === 'lg' ? 'lg' : ''));
@endphp

@if($layout === 'dropdown')
    <div class="btn-group btn-group-mobile">
        <button type="button" class="btn {{ $btnClass }} btn-light dropdown-toggle btn-mobile-touch" data-toggle="dropdown" aria-expanded="false" aria-label="Acciones" title="Ver, Editar, Eliminar">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right dropdown-menu-mobile dropdown-actions-fix" style="min-width: 140px;">
            @if($showRoute && (!$showPermission || auth()->user()->can($showPermission)))
                <a class="dropdown-item dropdown-item-mobile" href="{{ route($showRoute, $showParams) }}">
                    <i class="fas fa-eye mr-2"></i> Ver
                </a>
            @endif

            @if($editRoute && (!$editPermission || auth()->user()->can($editPermission)))
                <a class="dropdown-item dropdown-item-mobile" href="{{ route($editRoute, $editParams) }}">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            @endif

            @foreach($customActions as $action)
                @if(!isset($action['permission']) || auth()->user()->can($action['permission']))
                    <a class="dropdown-item dropdown-item-mobile {{ $action['class'] ?? '' }}"
                       href="{{ $action['href'] ?? '#' }}"
                       @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                       @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                        @if(isset($action['icon']))
                            <i class="fas {{ $action['icon'] }} mr-2"></i>
                        @endif
                        {{ $action['label'] }}
                    </a>
                @endif
            @endforeach

            @if($deleteRoute && (!$deletePermission || auth()->user()->can($deletePermission)))
                <div class="dropdown-divider"></div>
                <form action="{{ route($deleteRoute, $deleteParams) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item dropdown-item-mobile text-danger"
                            @if($confirmDelete) onclick="return confirm('{{ $deleteMessage }}')" @endif>
                        <i class="fas fa-trash mr-2"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>
@else
    <div class="btn-group btn-group-mobile" role="group">
        @if($showRoute && (!$showPermission || auth()->user()->can($showPermission)))
            <a href="{{ route($showRoute, $showParams) }}"
               class="btn {{ $btnClass }} btn-info btn-mobile-touch"
               title="Ver"
               aria-label="Ver">
                <i class="fas fa-eye"></i>
            </a>
        @endif

        @if($editRoute && (!$editPermission || auth()->user()->can($editPermission)))
            <a href="{{ route($editRoute, $editParams) }}"
               class="btn {{ $btnClass }} btn-warning btn-mobile-touch"
               title="Editar"
               aria-label="Editar">
                <i class="fas fa-edit"></i>
            </a>
        @endif

        @foreach($customActions as $action)
            @if(!isset($action['permission']) || auth()->user()->can($action['permission']))
                <a href="{{ $action['href'] ?? '#' }}"
                   class="btn {{ $btnClass }} {{ $action['btnClass'] ?? 'btn-secondary' }} btn-mobile-touch"
                   title="{{ $action['label'] ?? '' }}"
                   aria-label="{{ $action['label'] ?? '' }}"
                   @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                   @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                    @if(isset($action['icon']))
                        <i class="fas {{ $action['icon'] }}"></i>
                    @else
                        {{ $action['label'] }}
                    @endif
                </a>
            @endif
        @endforeach

        @if($deleteRoute && (!$deletePermission || auth()->user()->can($deletePermission)))
            <form action="{{ route($deleteRoute, $deleteParams) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="btn {{ $btnClass }} btn-danger btn-mobile-touch"
                        title="Eliminar"
                        aria-label="Eliminar"
                        @if($confirmDelete) onclick="return confirm('{{ $deleteMessage }}')" @endif>
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
@endif

<style>
    /* Mobile-first optimizations para action-buttons */
    @media (max-width: 767.98px) {
        .btn-group-mobile {
            width: 100%;
        }
        
        .btn-group-mobile .btn {
            flex: 1;
            min-height: 44px;
        }
        
        .dropdown-menu-mobile {
            min-width: 200px;
            max-width: 90vw;
        }
        
        .dropdown-item-mobile {
            min-height: 44px;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
        }
    }
</style>
