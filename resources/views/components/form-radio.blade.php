{{-- resources/views/components/form-radio.blade.php --}}
@props([
    'name',
    'options' => [], // Array asociativo ['value' => 'Label'] o array de objetos
    'selected' => null,
    'label' => null,
    'required' => false,
    'disabled' => false,
    'inline' => false,
    'helpText' => null,
    'optionValue' => null,
    'optionLabel' => null,
])

<div class="form-group">
    @if($label)
        <label class="d-block">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @foreach($options as $key => $option)
        @php
            $value = $optionValue ? (is_object($option) ? $option->{$optionValue} : $option[$optionValue]) : $key;
            $optLabel = $optionLabel ? (is_object($option) ? $option->{$optionLabel} : $option[$optionLabel]) : $option;
            $isSelected = old($name, $selected) == $value;
            $inputId = $name . '_' . $value;
        @endphp

        <div class="custom-control custom-radio {{ $inline ? 'custom-control-inline' : '' }}">
            <input
                type="radio"
                name="{{ $name }}"
                id="{{ $inputId }}"
                value="{{ $value }}"
                class="custom-control-input @error($name) is-invalid @enderror"
                @if($isSelected) checked @endif
                @if($disabled) disabled @endif
                @if($required && $loop->first) required @endif
                {{ $attributes }}
            >
            <label class="custom-control-label" for="{{ $inputId }}">
                {{ $optLabel }}
            </label>
        </div>
    @endforeach

    @error($name)
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif
</div>
