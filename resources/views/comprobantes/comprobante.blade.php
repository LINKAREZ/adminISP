<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $comprobante->numero_completo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: 80mm auto; margin: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #000; line-height: 1.2; background: #fff; width: 80mm; margin: 0 auto; }
        .container { width: 80mm; padding: 5mm 4mm; background: #fff; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; }
        .empresa-nombre { font-size: 12px; font-weight: 700; margin-bottom: 2px; text-transform: uppercase; }
        .empresa-info { font-size: 7px; line-height: 1.3; }
        .comprobante-tipo { font-size: 10px; font-weight: 700; text-transform: uppercase; margin: 3px 0; }
        .comprobante-numero { font-size: 14px; font-weight: 700; font-family: 'DejaVu Sans Mono', monospace; letter-spacing: 1px; margin: 2px 0; }
        .comprobante-fecha { font-size: 7px; margin-top: 2px; }
        .section { margin-bottom: 4px; padding: 3px 0; border-bottom: 1px dashed #ccc; }
        .section-title { font-size: 8px; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .info-line { font-size: 8px; margin-bottom: 1px; line-height: 1.3; }
        .info-label { font-weight: 600; }
        .badge { background: #000; color: #fff; padding: 1px 3px; font-size: 7px; font-weight: 600; margin-right: 3px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 8px; }
        .items-table thead { border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .items-table th { padding: 2px 1px; text-align: left; font-weight: 700; font-size: 7px; text-transform: uppercase; }
        .items-table th:last-child { text-align: right; }
        .items-table td { padding: 2px 1px; border-bottom: 1px dashed #ccc; font-size: 8px; }
        .items-table td:last-child { text-align: right; }
        .item-desc { font-weight: 500; }
        .item-detail { font-size: 7px; color: #666; margin-top: 1px; }
        .monto { font-family: 'DejaVu Sans Mono', monospace; font-weight: 600; }
        .totales { margin-top: 4px; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 0; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 8px; }
        .total-row:last-child { margin-bottom: 0; }
        .total-label { font-weight: 500; }
        .total-value { font-family: 'DejaVu Sans Mono', monospace; font-weight: 600; }
        .total-final { border-top: 1px dashed #000; padding-top: 2px; margin-top: 2px; }
        .total-final .total-label { font-weight: 700; font-size: 9px; }
        .total-final .total-value { font-weight: 700; font-size: 11px; }
        .monto-letras { margin: 3px 0; padding: 2px 0; border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; font-size: 7px; text-align: center; font-style: italic; }
        .codigo-box { text-align: center; margin: 3px 0; padding: 3px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; }
        .codigo-label { font-size: 7px; font-weight: 600; text-transform: uppercase; margin-bottom: 2px; }
        .codigo-value { font-size: 12px; font-weight: 700; font-family: 'DejaVu Sans Mono', monospace; letter-spacing: 1px; }
        .pago-section { margin: 4px 0; padding: 3px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; }
        .pago-line { font-size: 7px; margin-bottom: 1px; }
        .pago-label { font-weight: 600; text-transform: uppercase; }
        .pago-badge { background: #000; color: #fff; padding: 1px 3px; font-size: 7px; font-weight: 600; font-family: 'DejaVu Sans Mono', monospace; margin-left: 3px; }
        .footer { margin-top: 4px; padding-top: 3px; border-top: 1px dashed #000; text-align: center; font-size: 6px; line-height: 1.3; }
        .footer p { margin: 1px 0; }
        .estado-pagado { text-align: center; margin: 3px 0; padding: 2px; border: 1px solid #000; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .anulado-overlay { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 30px; font-weight: 700; color: rgba(0,0,0,0.2); text-transform: uppercase; letter-spacing: 4px; pointer-events: none; z-index: 100; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @if($comprobante->anulado)
        <div class="anulado-overlay">ANULADO</div>
    @endif
    <div class="container">
        <div class="header">
            <div class="empresa-nombre">{{ $empresa['nombre'] ?? config('app.name') }}</div>
            <div class="empresa-info">
                @if(!empty($empresa['ruc'])) RUC: {{ $empresa['ruc'] }} @endif
                @if(!empty($empresa['direccion']))<br>{{ $empresa['direccion'] }}@endif
                @if(!empty($empresa['telefono']))<br>Tel: {{ $empresa['telefono'] }}@endif
            </div>
            <div class="comprobante-tipo">RECIBO DE PAGO</div>
            <div class="comprobante-numero">{{ $comprobante->numero_completo }}</div>
            <div class="comprobante-fecha">{{ $comprobante->fecha_emision->format('d/m/Y H:i') }}</div>
        </div>
        <div class="section">
            <div class="section-title">Cliente</div>
            <div class="info-line"><span class="info-label">{{ $comprobante->cliente_nombre ?? $cliente->nombre ?? 'N/A' }}</span></div>
            <div class="info-line">
                <span class="badge">{{ strtoupper($comprobante->cliente_tipo_documento ?? $cliente->tipo_documento ?? 'DNI') }}</span>
                <span class="monto">{{ $comprobante->cliente_documento ?? $cliente->documento ?? 'N/A' }}</span>
            </div>
            @if($comprobante->cliente_direccion ?? ($cliente->direccion ?? null))
                <div class="info-line">{{ $comprobante->cliente_direccion ?? $cliente->direccion }}</div>
            @endif
        </div>
        @if(isset($pago) && $pago && $pago->recibo && $pago->recibo->servicio)
            @php $servicio = $pago->recibo->servicio; $plan = $servicio->plan ?? null; @endphp
            <div class="section">
                <div class="section-title">Servicio</div>
                @if($servicio->usuario_pppoe)<div class="info-line">Usuario: <span class="monto">{{ $servicio->usuario_pppoe }}</span></div>@endif
                @if($plan)<div class="info-line">Plan: <strong>{{ $plan->nombre }}</strong>@if($plan->velocidad_bajada_mbps)({{ $plan->velocidad_bajada_mbps }}/{{ $plan->velocidad_subida_mbps ?? 'N/A' }} Mbps)@endif</div>@endif
                @if($pago->recibo->periodo)<div class="info-line">Período: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $pago->recibo->periodo)->format('M/Y') }}</strong></div>@endif
            </div>
        @endif
        <table class="items-table">
            <thead><tr><th>DESCRIPCIÓN</th><th style="text-align:right;">TOTAL</th></tr></thead>
            <tbody>
                @forelse($comprobante->items as $item)
                    <tr>
                        <td>
                            <div class="item-desc">{{ $item->descripcion }}</div>
                            @if($item->descripcion_detalle)<div class="item-detail">{{ $item->descripcion_detalle }}</div>@endif
                            @if($item->periodo)<div class="item-detail">Período: {{ \Carbon\Carbon::createFromFormat('Y-m', $item->periodo)->format('M/Y') }}</div>@endif
                        </td>
                        <td class="monto">{{ formato_soles($item->total) }}</td>
                    </tr>
                @empty
                    @php $servicio = isset($pago) && $pago && $pago->recibo ? $pago->recibo->servicio : null; $plan = $servicio && $servicio->plan ? $servicio->plan : null; @endphp
                    <tr>
                        <td>
                            <div class="item-desc">Servicio de Internet @if($plan)- {{ $plan->nombre }}@endif</div>
                            @if($plan && ($plan->velocidad_bajada_mbps || $plan->velocidad_subida_mbps))<div class="item-detail">{{ $plan->velocidad_bajada_mbps ?? 'N/A' }}/{{ $plan->velocidad_subida_mbps ?? 'N/A' }} Mbps</div>@endif
                            @if(isset($pago) && $pago && $pago->recibo && $pago->recibo->periodo)<div class="item-detail">Período: {{ \Carbon\Carbon::createFromFormat('Y-m', $pago->recibo->periodo)->format('M/Y') }}</div>@endif
                        </td>
                        <td class="monto">{{ formato_soles($comprobante->monto) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="totales">
            <div class="total-row"><div class="total-label">Subtotal:</div><div class="total-value">{{ formato_soles($comprobante->subtotal ?? $comprobante->monto) }}</div></div>
            @if(!$comprobante->exonerado_igv && $comprobante->igv > 0)
                <div class="total-row"><div class="total-label">IGV:</div><div class="total-value">{{ formato_soles($comprobante->igv) }}</div></div>
            @endif
            <div class="total-row total-final"><div class="total-label">TOTAL:</div><div class="total-value">{{ formato_soles($comprobante->monto) }}</div></div>
        </div>
        <div class="monto-letras">Son: {{ ucfirst(\App\Core\Helpers\FormatHelper::numeroALetras($comprobante->monto)) }} Soles</div>
        @php
            $codigoRecibo = null;
            if (isset($pago) && $pago) {
                if ($pago->recibo && $pago->recibo->codigo) { $codigoRecibo = $pago->recibo->codigo; }
                elseif ($pago->recibo_id) {
                    $recibo = \App\Modules\Comprobantes\Models\Recibo::find($pago->recibo_id);
                    if ($recibo && $recibo->codigo) { $codigoRecibo = $recibo->codigo; }
                }
            }
        @endphp
        @if($codigoRecibo)
            <div class="codigo-box"><div class="codigo-label">Referencia de Pago</div><div class="codigo-value">{{ $codigoRecibo }}</div></div>
        @endif
        @if(isset($pago) && $pago)
            <div class="pago-section">
                <div class="section-title">Información de Pago</div>
                <div class="pago-line"><span class="pago-label">Medio:</span> {{ $pago->medio_pago_nombre ?? $pago->medioPago?->nombre ?? 'N/A' }}</div>
                <div class="pago-line"><span class="pago-label">Fecha:</span> {{ formato_fecha($pago->fecha_pago) }}@if($pago->fecha_hora) {{ $pago->fecha_hora->setTimezone('America/Lima')->format('H:i') }}@endif</div>
                @if($pago->numero_operacion)<div class="pago-line"><span class="pago-label">Operación:</span> <span class="pago-badge">{{ $pago->numero_operacion }}</span></div>@endif
                @if($pago->codigo_seguridad)<div class="pago-line"><span class="pago-label">Cód. Seg:</span> <span class="pago-badge">{{ $pago->codigo_seguridad }}</span></div>@endif
            </div>
        @endif
        @if(isset($pago) && $pago && $pago->recibo && $pago->recibo->estado === 'pagado')
            <div class="estado-pagado">✓ PAGO CONFIRMADO</div>
        @endif
        <div class="footer">
            <p><strong>Comprobante electrónico</strong></p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Este documento tiene validez como comprobante de pago interno.</p>
        </div>
    </div>
</body>
</html>
