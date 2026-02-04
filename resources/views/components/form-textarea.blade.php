{{-- resources/views/components/form-textarea.blade.php --}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 3,
    'maxlength' => null,
    'helpText' => null,
    'class' => '',
    'labelClass' => '',
    'showCount' => false, // Mostrar contador de caracteres
])

<div class="form-group form-group-mobile">
    @if($label)
        <label for="{{ $name }}" class="form-label-mobile {{ $labelClass }}">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder ?? $label }}"
        class="form-control form-control-mobile @error($name) is-invalid @enderror {{ $class }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($showCount) data-show-count="true" @endif
        {{ $attributes }}
    >{{ old($name, $value) }}</textarea>

    @if($showCount && $maxlength)
        <div class="d-flex justify-content-end">
            <small class="text-muted char-count" data-for="{{ $name }}">
                <span class="current">0</span>/{{ $maxlength }}
            </small>
        </div>
    @endif

    @error($name)
        <span class="invalid-feedback invalid-feedback-mobile" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted form-text-mobile">{{ $helpText }}</small>
    @endif
</div>

<style>
    /* Mobile-first optimizations para form-textarea */
    @media (max-width: 767.98px) {
        textarea.form-control-mobile {
            min-height: 88px;
            font-size: 16px; /* Previene zoom automático en iOS */
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            line-height: 1.5;
        }
    }
</style>

@if($showCount)
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-show-count="true"]').forEach(function(textarea) {
        const counter = document.querySelector('.char-count[data-for="' + textarea.id + '"] .current');
        if (counter) {
            counter.textContent = textarea.value.length;
            textarea.addEventListener('input', function() {
                counter.textContent = this.value.length;
            });
        }
    });
});
</script>
@endpush
@endonce
@endif
