<?php

namespace MenqzAdmin\Admin\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use ReflectionClass;

trait SmartCascadeSoftDeletes
{
    protected static function bootSmartCascadeSoftDeletes()
    {
        static::deleting(function (Model $model) {
            // 🔒 Validação customizada centralizada
            if (method_exists($model, 'guardDeletion')) {
                $model->guardDeletion(); // deve lançar exception se não puder excluir
            }
            foreach ($model->getCascadeDeletes() as $relation) {

                if (! method_exists($model, $relation)) {
                    continue;
                }

                $related = $model->$relation();

                if ($model->isForceDeleting()) {
                    $related->withTrashed()->get()->each->forceDelete();
                } else {
                    $related->get()->each->delete();
                }
            }
        });

        static::restoring(function (Model $model) {

            foreach ($model->getAutoCascadeRelations() as $relation) {
                $relation->withTrashed()->restore();
            }
        });
    }

    protected function getCascadeDeletes(): array
    {
        return property_exists($this, 'cascadeDeletes')
            ? $this->cascadeDeletes
            : [];
    }

    protected function throwDeletionException(string $message): void
    {
        throw ValidationException::withMessages([
            'error' => $message
        ]);
    }
}
