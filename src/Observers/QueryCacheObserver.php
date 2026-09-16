<?php

declare(strict_types=1);

namespace ByErikas\EloquentQueryCache\Observers;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Base QueryCacheable Observer that serves the purpose of flushing cache for the model it's observing
 *
 * Events that trigger a query cache flush: `saved`, `deleted`, `forceDeleted` and `restored`.
 * For all possible events see: https://laravel.com/docs/master/eloquent#events
 */
class QueryCacheObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Model "saved" event.
     */
    public function saved(Model $model): void
    {
        $this->flushCache($model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->flushCache($model);
    }

    /**
     * Handle the Model "forceDeleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->flushCache($model);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->flushCache($model);
    }

    /**
     * Handle the Model "belongsToManyAttached" event.
     * See: https://github.com/chelout/laravel-relationship-events for implementation
     */
    public function belongsToManyAttached(string $relation, Model $model, array $ids): void
    {
        $this->flushCache($model, $relation, $ids);
    }

    /**
     * Handle the Model "belongsToManyDetached" event.
     * See: https://github.com/chelout/laravel-relationship-events for implementation
     */
    public function belongsToManyDetached(string $relation, Model $model, array $ids): void
    {
        $this->flushCache($model, $relation, $ids);
    }

    /**
     * Handle the Model "belongsToManyUpdatedExistingPivot" event.
     * See: https://github.com/chelout/laravel-relationship-events for implementation
     */
    public function belongsToManyUpdatedExistingPivot(string $relation, Model $model, array $ids): void
    {
        $this->flushCache($model, $relation, $ids);
    }

    /**
     * Handle the Model "morphToManyAttached" event.
     * See: https://github.com/chelout/laravel-relationship-events for implementation
     */
    public function morphToManyAttached(string $relation, Model $model, array $ids): void
    {
        $this->flushCache($model, $relation, $ids);
    }

    /**
     * Handle the Model "morphToManyDetached" event.
     * See: https://github.com/chelout/laravel-relationship-events for implementation
     */
    public function morphToManyDetached(string $relation, Model $model, array $ids): void
    {
        $this->flushCache($model, $relation, $ids);
    }

    /**
     * Handle the Model "morphToManyUpdatedExistingPivot" event.
     * See: https://github.com/chelout/laravel-relationship-events for implementation
     */
    public function morphToManyUpdatedExistingPivot(string $relation, Model $model, array $ids): void
    {
        $this->flushCache($model, $relation, $ids);
    }

    /**
     * Invalidate the cache.
     * @throws Exception
     */
    protected function flushCache(Model $model, ?string $relation = null, ?array $ids = null): void
    {
        $tags = $model->getCacheTags();

        if (!$tags) {
            return;
        }

        $model::flushCache($tags);
    }
}
