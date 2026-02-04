{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE SELECT DE FORMULARIO                 ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico:                                                          ║
    ║  <x-form-select                                                       ║
    ║      name="estado"                                                    ║
    ║      label="Estado"                                                   ║
    ║      :options="['activo' => 'Activo', 'inactivo' => 'Inactivo']"      ║
    ║  />                                                                   ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Con colección de modelos:                                            ║
    ║  <x-form-select                                                       ║
    ║      name="cliente_id"                                                ║
    ║      label="Cliente"                                                  ║
    ║      :options="$clientes"                                             ║
    ║      option-value="id"                                                ║
    ║      option-label="nombre"                                            ║
    ║      :selected="$servicio->cliente_id"                                ║
    ║  />                                                                   ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - name: nombre del campo (requerido)
    - label: etiqueta del campo
    - options: array asociativo o colección
    - optionValue: clave para el valor (si es colección)
    - optionLabel: clave para el label (si es colección)
    - selected: valor seleccionado
    - placeholder: texto del primer option vacío
    - required: si es requerido
    - disabled: si está deshabilitado
    - icon: icono FontAwesome
    - help: texto de ayuda
--}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'optionValue' => 'id',
    'optionLabel' => 'nombre',
    'selected' => null,
    'placeholder' => 'Seleccione...',
    'required' => false,
    'disabled' => false,
    'icon' => null,
    'help' => null
])

@php
    $id = $name . '-' . uniqid();
    $hasError = $errors->has($name);
    $selectClass = 'form-control' . ($hasError ? ' is-invalid' : '');
    $selectedValue = old($name, $selected);
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

    <select
        name="{{ $name }}"
        id="{{ $id }}"
        {{ $attributes->merge(['class' => $selectClass . ' form-control-mobile']) }}
        @if($required) required @endif
        @if($disabled) disabled @endif
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if(is_array($options) && !empty($options))
            {{-- Array asociativo --}}
            @foreach($options as $value => $labelText)
                <option value="{{ $value }}" @selected($selectedValue == $value)>
                    {{ $labelText }}
                </option>
            @endforeach
        @elseif($options instanceof \Illuminate\Support\Collection || is_object($options))
            {{-- Colección de modelos --}}
            @foreach($options as $option)
                @php
                    $optValue = is_object($option) ? $option->{$optionValue} : $option[$optionValue];
                    $optLabel = is_object($option) ? $option->{$optionLabel} : $option[$optionLabel];
                @endphp
                <option value="{{ $optValue }}" @selected($selectedValue == $optValue)>
                    {{ $optLabel }}
                </option>
            @endforeach
        @endif
    </select>

    @error($name)
        <span class="invalid-feedback d-block invalid-feedback-mobile">{{ $message }}</span>
    @enderror

    @if($help)
        <small class="form-text text-muted form-text-mobile">{{ $help }}</small>
    @endif
</div>

<style>
    /* Mobile-first optimizations para form-select */
    @media (max-width: 767.98px) {
        .form-select.form-control-mobile,
        select.form-control-mobile {
            min-height: 44px;
            font-size: 16px; /* Previene zoom automático en iOS */
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
        }
    }
</style>
