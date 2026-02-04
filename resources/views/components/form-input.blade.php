{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE INPUT DE FORMULARIO                  ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico:                                                          ║
    ║  <x-form-input name="email" label="Correo" type="email" />            ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con valor y requerido:                                               ║
    ║  <x-form-input                                                        ║
    ║      name="nombre"                                                    ║
    ║      label="Nombre"                                                   ║
    ║      :value="$cliente->nombre"                                        ║
    ║      required                                                         ║
    ║  />                                                                   ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con icono y ayuda:                                                   ║
    ║  <x-form-input                                                        ║
    ║      name="password"                                                  ║
    ║      type="password"                                                  ║
    ║      label="Contraseña"                                               ║
    ║      icon="fa-lock"                                                   ║
    ║      help="Mínimo 8 caracteres"                                       ║
    ║  />                                                                   ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - name: nombre del campo (requerido)
    - label: etiqueta del campo
    - type: text, email, password, number, date, etc. (default: text)
    - value: valor del campo
    - placeholder: placeholder
    - required: si es requerido
    - disabled: si está deshabilitado
    - readonly: si es solo lectura
    - icon: icono FontAwesome
    - help: texto de ayuda
    - prepend: texto/HTML antes del input
    - append: texto/HTML después del input
--}}

@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'icon' => null,
    'help' => null,
    'prepend' => null,
    'append' => null,
    'min' => null,
    'max' => null,
    'step' => null,
])

@php
    $id = $name . '-' . uniqid();
    $hasError = $errors->has($name);
    $inputClass = 'form-control' . ($hasError ? ' is-invalid' : '');
@endphp

<div class="form-group form-group-mobile">
    @if($label)
        <label for="{{ $id }}" class="form-label-mobile">
            @if($icon)
                <i class="fas {{ $icon }} mr-1"></i>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    @if($prepend || $append)
        <div class="input-group input-group-mobile">
            @if($prepend)
                <div class="input-group-prepend">
                    <span class="input-group-text input-group-text-mobile">{{ $prepend }}</span>
                </div>
            @endif
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => $inputClass . ' form-control-mobile']) }}
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) max="{{ $max }}" @endif
        @if($step !== null) step="{{ $step }}" @endif
    >

    @if($prepend || $append)
            @if($append)
                <div class="input-group-append">
                    <span class="input-group-text input-group-text-mobile">{{ $append }}</span>
                </div>
            @endif
        </div>
    @endif

    @error($name)
        <span class="invalid-feedback d-block invalid-feedback-mobile">{{ $message }}</span>
    @enderror

    @if($help)
        <small class="form-text text-muted form-text-mobile">{{ $help }}</small>
    @endif
</div>

<style>
    /* Mobile-first optimizations para form-input */
    @media (max-width: 767.98px) {
        .form-group-mobile {
            margin-bottom: 1.25rem;
        }
        
        .form-label-mobile {
            font-size: 0.9375rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control-mobile {
            min-height: 44px;
            font-size: 16px; /* Previene zoom automático en iOS */
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
        }
        
        .input-group-mobile .form-control-mobile {
            min-height: 44px;
        }
        
        .input-group-text-mobile {
            min-height: 44px;
            display: flex;
            align-items: center;
        }
        
        .form-text-mobile {
            font-size: 0.8125rem;
            margin-top: 0.375rem;
        }
        
        .invalid-feedback-mobile {
            font-size: 0.8125rem;
            margin-top: 0.375rem;
        }
    }
</style>
