<?php

namespace App\Core\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Evento genérico para actualización de modelos
 */
class ModelUpdated extends BaseEvent
{
    public Model $model;
    public string $modelType;
    public array $changes;

    public function __construct(Model $model, array $changes = [])
    {
        parent::__construct();

        $this->model = $model;
        $this->modelType = get_class($model);
        $this->changes = $changes ?: $model->getChanges();
    }

    public function toLogArray(): array
    {
        return array_merge(parent::toLogArray(), [
            'model_type' => $this->modelType,
            'model_id' => $this->model->getKey(),
            'changes' => $this->changes,
        ]);
    }
}
