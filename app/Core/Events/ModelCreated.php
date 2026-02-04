<?php

namespace App\Core\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Evento genérico para creación de modelos
 */
class ModelCreated extends BaseEvent
{
    public Model $model;
    public string $modelType;

    public function __construct(Model $model)
    {
        parent::__construct();

        $this->model = $model;
        $this->modelType = get_class($model);
    }

    public function toLogArray(): array
    {
        return array_merge(parent::toLogArray(), [
            'model_type' => $this->modelType,
            'model_id' => $this->model->getKey(),
        ]);
    }
}
