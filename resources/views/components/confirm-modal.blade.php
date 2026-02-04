{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║                    COMPONENTE DE MODAL DE CONFIRMACIÓN                ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Uso básico (con JavaScript):                                         ║
    ║  <x-confirm-modal id="deleteModal" title="Eliminar cliente">          ║
    ║      ¿Está seguro de eliminar este cliente?                           ║
    ║  </x-confirm-modal>                                                   ║
    ║                                                                       ║
    ║  // JavaScript para abrir:                                            ║
    ║  ConfirmModal.show('deleteModal', {                                   ║
    ║      action: '/clientes/1',                                           ║
    ║      method: 'DELETE'                                                 ║
    ║  });                                                                  ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  Modal de peligro:                                                    ║
    ║  <x-confirm-modal                                                     ║
    ║      id="deleteModal"                                                 ║
    ║      title="Eliminar"                                                 ║
    ║      variant="danger"                                                 ║
    ║      confirm-text="Eliminar"                                          ║
    ║  >                                                                    ║
    ║      Esta acción no se puede deshacer.                                ║
    ║  </x-confirm-modal>                                                   ║
    ╚══════════════════════════════════════════════════════════════════════╝

    @props:
    - id: ID único del modal (requerido)
    - title: título del modal
    - variant: primary, danger, warning (default: danger)
    - confirmText: texto del botón de confirmar
    - cancelText: texto del botón de cancelar
    - icon: icono del header
--}}

@props([
    'id',
    'title' => 'Confirmar acción',
    'variant' => 'danger',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'icon' => 'fa-exclamation-triangle'
])

@php
    $headerClass = match($variant) {
        'danger' => 'bg-danger text-white',
        'warning' => 'bg-warning',
        'success' => 'bg-success text-white',
        default => 'bg-primary text-white'
    };

    $btnClass = match($variant) {
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'success' => 'btn-success',
        default => 'btn-primary'
    };
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header {{ $headerClass }}">
                <h5 class="modal-title" id="{{ $id }}Label">
                    <i class="fas {{ $icon }} mr-2"></i>{{ $title }}
                </h5>
                <button type="button" class="close {{ $variant === 'warning' ? '' : 'text-white' }}" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirm-modal-content">
                    {{ $slot }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>{{ $cancelText }}
                </button>
                <form id="{{ $id }}-form" method="POST" action="" class="d-inline">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE" id="{{ $id }}-method">
                    <button type="submit" class="btn {{ $btnClass }}">
                        <i class="fas fa-check mr-1"></i>{{ $confirmText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
window.ConfirmModal = {
    show: function(modalId, options) {
        options = options || {};
        var modal = document.getElementById(modalId);
        var form = document.getElementById(modalId + '-form');
        var methodInput = document.getElementById(modalId + '-method');

        if (form && options.action) {
            form.action = options.action;
        }

        if (methodInput && options.method) {
            methodInput.value = options.method;
        }

        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#' + modalId).modal('show');
        } else {
            modal.classList.add('show');
            modal.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    },

    hide: function(modalId) {
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#' + modalId).modal('hide');
        } else {
            var modal = document.getElementById(modalId);
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }
};
</script>
@endpush
@endonce
