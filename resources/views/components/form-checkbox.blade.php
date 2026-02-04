{{-- resources/views/components/form-checkbox.blade.php --}}
@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'required' => false,
    'switch' => false, // Usar estilo switch
    'inline' => false,
    'helpText' => null,
    'class' => '',
])

@php
    $isChecked = old($name, $checked) ? true : false;
    $wrapperClass = $switch ? 'custom-control custom-switch' : 'custom-control custom-checkbox';
    if ($inline) {
        $wrapperClass .= ' custom-control-inline';
    }
@endphp

<div class="{{ $wrapperClass }} form-checkbox-mobile">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        class="custom-control-input @error($name) is-invalid @enderror {{ $class }}"
        @if($isChecked) checked @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        {{ $attributes }}
    >
    <label class="custom-control-label form-checkbox-label-mobile" for="{{ $name }}">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>

    @error($name)
        <span class="invalid-feedback invalid-feedback-mobile" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted d-block form-text-mobile">{{ $helpText }}</small>
    @endif
</div>

<style>
    /* Mobile-first optimizations para form-checkbox */
    @media (max-width: 767.98px) {
        .form-checkbox-mobile {
            padding-left: 2rem;
            min-height: 44px;
            display: flex;
            align-items: center;
        }
        
        .form-checkbox-mobile .custom-control-input {
            width: 20px;
            height: 20px;
            margin-top: 0;
        }
        
        .form-checkbox-label-mobile {
            font-size: 0.9375rem;
            padding-left: 0.5rem;
            margin-bottom: 0;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Área táctil más grande para el checkbox */
        .form-checkbox-mobile::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 44px;
            height: 44px;
            z-index: 1;
        }
    }
</style>
