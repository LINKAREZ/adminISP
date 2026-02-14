{{-- Wizard de configuración inicial para ISPs nuevos --}}
@props(['mostrar' => false])

@if($mostrar)
<div id="onboarding-wizard-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-rocket mr-2"></i>Configuración inicial</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="dismissOnboarding()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Sigue estos pasos para empezar a usar tu panel. Puedes omitirlos y configurar más tarde.</p>
                <div class="list-group list-group-flush">
                    <a href="{{ route('red.routers.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                        <span><i class="fas fa-server text-primary mr-2"></i>1. Configura tu router</span>
                        <i class="fas fa-external-link-alt text-muted small"></i>
                    </a>
                    <a href="{{ route('servicios.planes.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                        <span><i class="fas fa-wifi text-primary mr-2"></i>2. Crea planes de internet</span>
                        <i class="fas fa-external-link-alt text-muted small"></i>
                    </a>
                    <a href="{{ route('clientes.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                        <span><i class="fas fa-users text-primary mr-2"></i>3. Registra tu primer cliente</span>
                        <i class="fas fa-external-link-alt text-muted small"></i>
                    </a>
                    <a href="{{ route('comprobantes.dashboard-finanzas') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                        <span><i class="fas fa-file-invoice text-primary mr-2"></i>4. Genera recibos</span>
                        <i class="fas fa-external-link-alt text-muted small"></i>
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="dismissOnboarding()">
                    <i class="fas fa-clock mr-1"></i>Configurar después
                </button>
                <button type="button" class="btn btn-primary" onclick="dismissOnboarding()">
                    <i class="fas fa-check mr-1"></i>Entendido
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function dismissOnboarding() {
        fetch('{{ route("onboarding.completar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        }).then(function() {
            $('#onboarding-wizard-modal').modal('hide');
            document.getElementById('onboarding-wizard-modal').remove();
        }).catch(function() {
            $('#onboarding-wizard-modal').modal('hide');
        });
    }
    window.dismissOnboarding = dismissOnboarding;

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#onboarding-wizard-modal').modal('show');
        }
    });
})();
</script>
@endpush
@endif
