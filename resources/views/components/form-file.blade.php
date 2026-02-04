{{-- resources/views/components/form-file.blade.php --}}
@props([
    'name',
    'label' => null,
    'accept' => null, // Tipos de archivo aceptados: 'image/*', '.pdf', etc.
    'multiple' => false,
    'required' => false,
    'disabled' => false,
    'helpText' => null,
    'preview' => false, // Mostrar preview de imagen
    'currentFile' => null, // URL del archivo actual
    'class' => '',
])

<div class="form-group">
    @if($label)
        <label for="{{ $name }}">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @if($preview && $currentFile)
        <div class="mb-2">
            <img src="{{ $currentFile }}"
                 alt="Archivo actual"
                 class="img-thumbnail"
                 style="max-height: 150px;"
                 id="preview-{{ $name }}">
        </div>
    @elseif($preview)
        <div class="mb-2" id="preview-container-{{ $name }}" style="display: none;">
            <img src=""
                 alt="Vista previa"
                 class="img-thumbnail"
                 style="max-height: 150px;"
                 id="preview-{{ $name }}">
        </div>
    @endif

    <div class="custom-file">
        <input
            type="file"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $name }}"
            class="custom-file-input @error($name) is-invalid @enderror {{ $class }}"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
        >
        <label class="custom-file-label" for="{{ $name }}" data-browse="Examinar">
            Seleccionar archivo{{ $multiple ? 's' : '' }}...
        </label>

        @error($name)
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar etiqueta del archivo
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const label = this.nextElementSibling;
            const files = this.files;

            if (files.length > 1) {
                label.textContent = files.length + ' archivos seleccionados';
            } else if (files.length === 1) {
                label.textContent = files[0].name;
            } else {
                label.textContent = 'Seleccionar archivo...';
            }

            // Preview de imagen
            const previewId = 'preview-' + this.id;
            const containerId = 'preview-container-' + this.id;
            const preview = document.getElementById(previewId);
            const container = document.getElementById(containerId);

            if (preview && files.length > 0 && files[0].type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    if (container) container.style.display = 'block';
                };
                reader.readAsDataURL(files[0]);
            }
        });
    });
});
</script>
@endpush
@endonce
