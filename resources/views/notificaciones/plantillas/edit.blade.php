@extends('layouts.adminlte')

@section('title', 'Editar Plantilla de WhatsApp')
@section('page-title', 'Editar Plantilla de WhatsApp')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Plantillas de WhatsApp', 'route' => 'notificaciones.plantillas.index'],
        ['label' => 'Editar']
    ]" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-whatsapp mr-2" style="color: #25D366;"></i>Editar Plantilla
                    </h3>
                </div>
                <form action="{{ route('notificaciones.plantillas.update', $plantillaWhatsApp) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <input type="text"
                                   class="form-control"
                                   id="tipo"
                                   value="{{ $plantillaWhatsApp->tipo }}"
                                   disabled>
                            <small class="form-text text-muted">El tipo no se puede modificar</small>
                        </div>

                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   id="nombre"
                                   name="nombre"
                                   value="{{ old('nombre', $plantillaWhatsApp->nombre) }}"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mensaje">Mensaje</label>
                            <textarea class="form-control @error('mensaje') is-invalid @enderror"
                                      id="mensaje"
                                      name="mensaje"
                                      rows="10"
                                      required>{{ old('mensaje', $plantillaWhatsApp->mensaje) }}</textarea>
                            @error('mensaje')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Variables disponibles:
                                <code>{cliente}</code>,
                                <code>{documento}</code>,
                                <code>{monto}</code>,
                                <code>{codigo_recibo}</code>,
                                <code>{fecha_vencimiento}</code>,
                                <code>{dias_vencido}</code>,
                                <code>{plan}</code>,
                                <code>{empresa}</code>
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="activo"
                                       name="activo"
                                       value="1"
                                       {{ old('activo', $plantillaWhatsApp->activo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">Activo</label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Vista Previa</h5>
                            <div id="vista-previa" class="mt-2 p-3 bg-light rounded" style="white-space: pre-wrap;"></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Guardar Cambios
                        </button>
                        <a href="{{ route('notificaciones.plantillas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        'use strict';
        const logDebug = (...args) => {
            if (window.logger && typeof window.logger.debug === 'function') {
                window.logger.debug(...args);
                return;
            }
            if (console && typeof console.debug === 'function') {
                console.debug(...args);
            }
        };
        const logWarn = (...args) => {
            if (window.logger && typeof window.logger.warn === 'function') {
                window.logger.warn(...args);
                return;
            }
            if (console && typeof console.warn === 'function') {
                console.warn(...args);
            }
        };
        const logError = (...args) => {
            if (window.logger && typeof window.logger.error === 'function') {
                window.logger.error(...args);
                return;
            }
            if (console && typeof console.error === 'function') {
                console.error(...args);
            }
        };
        const console = { log: logDebug, warn: logWarn, error: logError };

        // Manejo de errores para evitar conflictos con extensiones del navegador
        try {
            const mensajeTextarea = document.getElementById('mensaje');
            const vistaPrevia = document.getElementById('vista-previa');

            if (!mensajeTextarea || !vistaPrevia) {
                console.warn('Elementos del formulario no encontrados');
                return;
            }

            function actualizarVistaPrevia() {
                try {
                    let mensaje = mensajeTextarea.value;

                    // Reemplazar variables con ejemplos
                    const ejemplos = {
                        '{cliente}': 'Juan Pérez',
                        '{documento}': '12345678',
                        '{monto}': '150.00',
                        '{codigo_recibo}': '12345678202501U1',
                        '{fecha_vencimiento}': '15/01/2025',
                        '{dias_vencido}': '5',
                        '{plan}': 'Plan 50 Mbps',
                        '{empresa}': 'Admin ISP'
                    };

                    Object.keys(ejemplos).forEach(variable => {
                        mensaje = mensaje.replace(new RegExp(variable.replace(/[{}]/g, '\\$&'), 'g'), ejemplos[variable]);
                    });

                    vistaPrevia.textContent = mensaje;
                } catch (error) {
                    console.error('Error al actualizar vista previa:', error);
                }
            }

            // Usar addEventListener con opciones para evitar conflictos
            mensajeTextarea.addEventListener('input', actualizarVistaPrevia, {
                passive: true,
                once: false
            });

            // Inicializar vista previa
            actualizarVistaPrevia();
        } catch (error) {
            console.error('Error al inicializar editor de plantilla:', error);
        }
    })();
</script>
@endpush
