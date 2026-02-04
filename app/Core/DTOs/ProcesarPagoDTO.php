<?php

namespace App\Core\DTOs;

class ProcesarPagoDTO
{
    public function __construct(
        public int $clienteId,
        public float $monto,
        public string $fechaPago,
        public ?int $servicioId = null,
        public ?int $reciboId = null,
        public ?string $medioPago = null,
        public ?int $medioPagoId = null,
        public ?string $codigoSeguridad = null,
        public ?string $numeroOperacion = null,
        public ?string $referencia = null,
        public ?string $notas = null,
        public ?int $registradoPor = null,
    ) {}

    /**
     * Crear desde array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clienteId: $data['cliente_id'],
            monto: (float) $data['monto'],
            fechaPago: $data['fecha_pago'],
            servicioId: $data['servicio_id'] ?? null,
            reciboId: $data['recibo_id'] ?? null,
            medioPago: $data['medio_pago'] ?? null,
            medioPagoId: $data['medio_pago_id'] ?? null,
            codigoSeguridad: $data['codigo_seguridad'] ?? null,
            numeroOperacion: $data['numero_operacion'] ?? null,
            referencia: $data['referencia'] ?? null,
            notas: $data['notas'] ?? null,
            registradoPor: $data['registrado_por'] ?? null,
        );
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'cliente_id' => $this->clienteId,
            'monto' => $this->monto,
            'fecha_pago' => $this->fechaPago,
            'servicio_id' => $this->servicioId,
            'recibo_id' => $this->reciboId,
            'medio_pago' => $this->medioPago,
            'medio_pago_id' => $this->medioPagoId,
            'codigo_seguridad' => $this->codigoSeguridad,
            'numero_operacion' => $this->numeroOperacion,
            'referencia' => $this->referencia,
            'notas' => $this->notas,
            'registrado_por' => $this->registradoPor,
        ];
    }
}
