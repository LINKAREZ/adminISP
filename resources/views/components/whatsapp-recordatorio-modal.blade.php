{{--
    Modal para mostrar el mensaje de recordatorio de pago y el número de teléfono
    para envío manual por WhatsApp
--}}

<div class="modal fade" id="modalWhatsAppRecordatorio" tabindex="-1" role="dialog" aria-labelledby="modalWhatsAppRecordatorioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalWhatsAppRecordatorioLabel">
                    <i class="fab fa-whatsapp mr-2"></i>Recordatorio de Pago - WhatsApp
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Instrucciones:</strong> Copia el mensaje y el número de teléfono, luego envíalo manualmente por WhatsApp.
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">
                        <i class="fas fa-user mr-2 text-primary"></i>Cliente:
                    </label>
                    <p class="mb-0" id="whatsapp-cliente-nombre">-</p>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">
                        <i class="fas fa-phone mr-2 text-success"></i>Número de Teléfono:
                    </label>
                    <div class="input-group">
                        <input 
                            type="text" 
                            class="form-control font-weight-bold text-success" 
                            id="whatsapp-telefono" 
                            readonly
                            value=""
                            style="font-size: 1.1rem;"
                        >
                        <div class="input-group-append">
                            <button 
                                type="button" 
                                class="btn btn-success" 
                                id="btn-copiar-telefono"
                                title="Copiar número"
                            >
                                <i class="fas fa-copy"></i>
                            </button>
                            <a 
                                href="#" 
                                class="btn btn-success" 
                                id="btn-abrir-whatsapp"
                                target="_blank"
                                title="Abrir WhatsApp"
                            >
                                <i class="fab fa-whatsapp"></i> Abrir WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">
                        <i class="fas fa-comment-alt mr-2 text-info"></i>Mensaje:
                    </label>
                    <textarea 
                        class="form-control" 
                        id="whatsapp-mensaje" 
                        rows="10" 
                        readonly
                        style="font-family: monospace; white-space: pre-wrap;"
                    ></textarea>
                    <button 
                        type="button" 
                        class="btn btn-sm btn-outline-primary mt-2" 
                        id="btn-copiar-mensaje"
                    >
                        <i class="fas fa-copy mr-1"></i> Copiar Mensaje
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function() {
    'use strict';

    let listenersInicializados = false;

    // Inicializar listeners una sola vez
    function inicializarListeners() {
        if (listenersInicializados) return;
        listenersInicializados = true;

        // Botón copiar teléfono
        document.getElementById('btn-copiar-telefono').addEventListener('click', function() {
            const telefonoInput = document.getElementById('whatsapp-telefono');
            telefonoInput.select();
            telefonoInput.setSelectionRange(0, 99999); // Para móviles
            document.execCommand('copy');
            
            // Feedback visual
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
            }, 2000);
        });

        // Botón copiar mensaje
        document.getElementById('btn-copiar-mensaje').addEventListener('click', function() {
            const mensajeTextarea = document.getElementById('whatsapp-mensaje');
            mensajeTextarea.select();
            mensajeTextarea.setSelectionRange(0, 99999); // Para móviles
            document.execCommand('copy');
            
            // Feedback visual
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copiado!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        });
    }

    // Función para mostrar el modal con los datos
    window.mostrarModalWhatsApp = function(data) {
        // Inicializar listeners si no están inicializados
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', inicializarListeners);
        } else {
            inicializarListeners();
        }

        // Llenar los campos del modal
        document.getElementById('whatsapp-cliente-nombre').textContent = data.cliente || '-';
        document.getElementById('whatsapp-telefono').value = data.telefono_formateado || data.telefono || '';
        document.getElementById('whatsapp-mensaje').value = data.mensaje || '';

        // Actualizar enlace de WhatsApp
        const telefonoParaUrl = (data.telefono || '').replace(/\+/g, '').replace(/\s/g, '');
        const mensajeCodificado = encodeURIComponent(data.mensaje || '');
        const whatsappUrl = `https://wa.me/${telefonoParaUrl}?text=${mensajeCodificado}`;
        document.getElementById('btn-abrir-whatsapp').href = whatsappUrl;

        // Abrir modal
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#modalWhatsAppRecordatorio').modal('show');
        } else {
            const modal = document.getElementById('modalWhatsAppRecordatorio');
            modal.classList.add('show');
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    };

    // Inicializar listeners cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarListeners);
    } else {
        inicializarListeners();
    }
})();
</script>
@endpush
@endonce
