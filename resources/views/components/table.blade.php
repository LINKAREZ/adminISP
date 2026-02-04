{{-- resources/views/components/table.blade.php --}}
@props([
    'id' => 'dataTable',
    'striped' => true,
    'bordered' => true,
    'hover' => true,
    'responsive' => true,
    'small' => false,
    'class' => '',
    'datatable' => true, // Activar DataTables
    'datatableOptions' => [], // Opciones adicionales de DataTables
    'exportable' => false, // Mostrar botones de exportación
    'searchable' => true, // Mostrar buscador
    'paginate' => true, // Mostrar paginación
    'pageLength' => 10, // Registros por página
    'ordering' => true, // Permitir ordenamiento
    'order' => [[0, 'asc']], // Orden por defecto
    'language' => 'es', // Idioma (es, en)
])

@php
    $tableClasses = [
        'table',
        $striped ? 'table-striped' : '',
        $bordered ? 'table-bordered' : '',
        $hover ? 'table-hover' : '',
        $small ? 'table-sm' : '',
        $class,
    ];
    $combinedClass = implode(' ', array_filter($tableClasses));

    $wrapperClass = $responsive ? 'table-responsive' : '';
@endphp

<div class="{{ $wrapperClass }}">
    <table id="{{ $id }}" {{ $attributes->merge(['class' => $combinedClass]) }}>
        @if(isset($head))
            <thead class="thead-light">
                {{ $head }}
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
        @if(isset($foot))
            <tfoot>
                {{ $foot }}
            </tfoot>
        @endif
    </table>
</div>

@if($datatable)
@push('scripts')
<script>
(function() {
    'use strict';

    function initDataTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            setTimeout(initDataTable, 100);
            return;
        }

        if ($.fn.DataTable.isDataTable('#{{ $id }}')) {
            return; // Ya inicializado
        }

        const languageES = {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        };

        const defaultOptions = {
            responsive: {{ $responsive ? 'true' : 'false' }},
            searching: {{ $searchable ? 'true' : 'false' }},
            paging: {{ $paginate ? 'true' : 'false' }},
            pageLength: {{ $pageLength }},
            ordering: {{ $ordering ? 'true' : 'false' }},
            order: @json($order),
            language: '{{ $language }}' === 'es' ? languageES : {},
            @if($exportable)
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            @endif
        };

        const customOptions = @json($datatableOptions);
        const finalOptions = Object.assign({}, defaultOptions, customOptions);

        $('#{{ $id }}').DataTable(finalOptions);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDataTable);
    } else {
        initDataTable();
    }
})();
</script>
@endpush
@endif
