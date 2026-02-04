{{-- resources/views/components/form-group.blade.php --}}
@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'helpText' => null,
    'icon' => null,
    'horizontal' => false,
    'labelCol' => 'col-sm-3',
    'inputCol' => 'col-sm-9',
])

<div class="form-group {{ $horizontal ? 'row' : '' }}">
    @if($label)
        <label
            @if($name) for="{{ $name }}" @endif
            class="{{ $horizontal ? $labelCol . ' col-form-label' : '' }}"
        >
            @if($icon)
                <i class="fas {{ $icon }} mr-1 text-muted"></i>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="{{ $horizontal ? $inputCol : '' }}">
        {{ $slot }}

        @if($name)
            @error($name)
                <span class="invalid-feedback d-block">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        @endif

        @if($helpText)
            <small class="form-text text-muted">{{ $helpText }}</small>
        @endif
    </div>
</div>
