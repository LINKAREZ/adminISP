<?php

namespace App\Core\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Evento genérico para eliminación de modelos
 */
class ModelDeleted extends BaseEvent
{
    public int $modelId;
    public string $modelType;
    public array $modelData;

    public function __construct(Model $model)
    {
        parent::__construct();

        $this->modelId = $model->getKey();
        $this->modelType = get_class($model);
        $this->modelData = $model->toArray();
    }

    public function toLogArray(): array
    {
        return array_merge(parent::toLogArray(), [
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
        ]);
    }
}
