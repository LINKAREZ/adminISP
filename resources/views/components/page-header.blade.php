{{-- resources/views/components/page-header.blade.php --}}
@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'breadcrumbs' => [], // Array de items para breadcrumb
    'actions' => null, // Slot para botones de acción
])

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    @if($icon)
                        <i class="fas {{ $icon }} mr-2 text-muted"></i>
                    @endif
                    {{ $title }}
                    @if($subtitle)
                        <small class="text-muted ml-2">{{ $subtitle }}</small>
                    @endif
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="d-flex justify-content-end align-items-center">
                    @if($actions)
                        <div class="mr-3">
                            {{ $actions }}
                        </div>
                    @endif

                    @if(count($breadcrumbs) > 0)
                        <x-breadcrumb :items="$breadcrumbs" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
