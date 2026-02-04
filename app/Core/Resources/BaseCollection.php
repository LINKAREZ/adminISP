<?php

namespace App\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Clase base para colecciones de API Resources
 */
abstract class BaseCollection extends ResourceCollection
{
    /**
     * Transformar la colección en un array
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => $this->getMeta(),
        ];
    }

    /**
     * Obtener metadata de la colección
     */
    protected function getMeta(): array
    {
        $meta = [
            'total' => $this->collection->count(),
        ];

        // Si es paginado, agregar info de paginación
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $meta = array_merge($meta, [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
            ]);
        }

        return $meta;
    }

    /**
     * Agregar información adicional a la respuesta
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
        ];
    }

    /**
     * Personalizar la respuesta paginada
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
                'per_page' => $paginated['per_page'],
                'total' => $paginated['total'],
                'from' => $paginated['from'],
                'to' => $paginated['to'],
            ],
            'links' => [
                'first' => $paginated['first_page_url'],
                'last' => $paginated['last_page_url'],
                'prev' => $paginated['prev_page_url'],
                'next' => $paginated['next_page_url'],
            ],
        ];
    }
}
