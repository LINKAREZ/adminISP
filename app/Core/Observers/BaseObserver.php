<?php

namespace App\Core\Observers;

use App\Core\Events\ModelCreated;
use App\Core\Events\ModelDeleted;
use App\Core\Events\ModelUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Clase base para Observers
 */
abstract class BaseObserver
{
    /**
     * Disparar eventos genéricos
     */
    protected bool $dispatchEvents = true;

    /**
     * Registrar en logs
     */
    protected bool $logActivity = true;

    /**
     * Modelo creado
     */
    public function created(Model $model): void
    {
        if ($this->logActivity) {
            $this->logAction('created', $model);
        }

        if ($this->dispatchEvents) {
            event(new ModelCreated($model));
        }

        $this->afterCreated($model);
    }

    /**
     * Modelo actualizado
     */
    public function updated(Model $model): void
    {
        if ($this->logActivity) {
            $this->logAction('updated', $model, $model->getChanges());
        }

        if ($this->dispatchEvents) {
            event(new ModelUpdated($model));
        }

        $this->afterUpdated($model);
    }

    /**
     * Modelo eliminado
     */
    public function deleted(Model $model): void
    {
        if ($this->logActivity) {
            $this->logAction('deleted', $model);
        }

        if ($this->dispatchEvents) {
            event(new ModelDeleted($model));
        }

        $this->afterDeleted($model);
    }

    /**
     * Modelo restaurado (si usa SoftDeletes)
     */
    public function restored(Model $model): void
    {
        if ($this->logActivity) {
            $this->logAction('restored', $model);
        }

        $this->afterRestored($model);
    }

    /**
     * Antes de crear
     */
    public function creating(Model $model): void
    {
        $this->beforeCreating($model);
    }

    /**
     * Antes de actualizar
     */
    public function updating(Model $model): void
    {
        $this->beforeUpdating($model);
    }

    /**
     * Antes de eliminar
     */
    public function deleting(Model $model): void
    {
        $this->beforeDeleting($model);
    }

    // Métodos hook para sobreescribir en observers hijos
    protected function beforeCreating(Model $model): void {}
    protected function afterCreated(Model $model): void {}
    protected function beforeUpdating(Model $model): void {}
    protected function afterUpdated(Model $model): void {}
    protected function beforeDeleting(Model $model): void {}
    protected function afterDeleted(Model $model): void {}
    protected function afterRestored(Model $model): void {}

    /**
     * Log de acción
     */
    protected function logAction(string $action, Model $model, array $extra = []): void
    {
        if (!config('app.debug')) {
            return;
        }

        $data = [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'user_id' => auth()->id(),
        ];

        if (!empty($extra)) {
            $data['changes'] = $extra;
        }

        Log::channel('models')->info("[{$action}] " . class_basename($model), $data);
    }

    /**
     * Obtener nombre del observer
     */
    protected function getObserverName(): string
    {
        return class_basename(static::class);
    }
}
