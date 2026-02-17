<p>Hola,</p>
<p>Le recordamos que su recibo {{ $recibo->codigo }} vence el {{ $recibo->fecha_vencimiento->format('d/m/Y') }}.</p>
<p>Monto: {{ config('isp.comprobantes.simbolo_moneda', 'S/.') }} {{ number_format($recibo->monto, 2) }}</p>
<p>{{ config('isp.empresa.nombre', 'Admin ISP') }}</p>
