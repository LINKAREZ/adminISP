@props([
    'href' => '#',
    'label' => '',
    'isActive' => false,
    'permission' => true
])

@if($permission)
    <a
        href="{{ $href }}"
        class="nav-link d-flex align-items-center px-3 py-2 small font-weight-medium rounded mb-1 {{ $isActive ? 'active' : '' }}"
        role="menuitem"
        data-sidebar-link="true"
    >
        <span class="flex-fill">{{ $label }}</span>
    </a>
@endif
