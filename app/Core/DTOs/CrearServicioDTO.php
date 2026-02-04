<?php

namespace App\Core\DTOs;

class CrearServicioDTO
{
    public function __construct(
        public int $clienteId,
        public int $routerId,
        public int $planId,
        public string $tipoPppoe,
        public ?string $usuarioPppoe = null,
        public ?string $passwordPppoe = null,
        public ?string $macAddress = null,
        public string $estado = 'activo',
        public ?int $ubicacionId = null,
        public ?string $ubicacionDireccion = null,
        public ?string $ubicacionReferencia = null,
        public ?string $ubicacionDistrito = null,
        public ?string $ubicacionProvincia = null,
        public ?string $ubicacionDepartamento = null,
        public ?int $onuId = null,
        public ?string $notas = null,
        public bool $esProvisional = false,
    ) {}

    /**
     * Crear desde array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clienteId: $data['cliente_id'],
            routerId: $data['router_id'],
            planId: $data['plan_id'],
            tipoPppoe: $data['tipo_pppoe'],
            usuarioPppoe: $data['usuario_pppoe'] ?? null,
            passwordPppoe: $data['password_pppoe'] ?? null,
            macAddress: $data['mac_address'] ?? null,
            estado: $data['estado'] ?? 'activo',
            ubicacionId: $data['ubicacion_id'] ?? null,
            ubicacionDireccion: $data['ubicacion_direccion'] ?? null,
            ubicacionReferencia: $data['ubicacion_referencia'] ?? null,
            ubicacionDistrito: $data['ubicacion_distrito'] ?? null,
            ubicacionProvincia: $data['ubicacion_provincia'] ?? null,
            ubicacionDepartamento: $data['ubicacion_departamento'] ?? null,
            onuId: $data['onu_id'] ?? null,
            notas: $data['notas'] ?? null,
            esProvisional: $data['es_provisional'] ?? false,
        );
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'cliente_id' => $this->clienteId,
            'router_id' => $this->routerId,
            'plan_id' => $this->planId,
            'tipo_pppoe' => $this->tipoPppoe,
            'usuario_pppoe' => $this->usuarioPppoe,
            'password_pppoe' => $this->passwordPppoe,
            'mac_address' => $this->macAddress,
            'estado' => $this->estado,
            'ubicacion_id' => $this->ubicacionId,
            'notas' => $this->notas,
            'es_provisional' => $this->esProvisional,
        ];
    }
}

