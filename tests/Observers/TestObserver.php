<?php

declare(strict_types=1);

namespace Tests\Observers;

use ByErikas\EloquentQueryCache\Observers\QueryCacheObserver;
use Illuminate\Database\Eloquent\Model;
use Exception;

/**
 * Base QueryCacheable Observer that serves the purpose of flushing cache for the model it's observing
 *
 * Events that trigger a query cache flush: `saved`, `deleted`, `forceDeleted` and `restored`.
 * For all possible events see: https://laravel.com/docs/master/eloquent#events
 */
class TestObserver extends QueryCacheObserver
{
    /**
     * Handle the Model "saved" event.
     */
    public function saved(Model $model): void
    {
        // 
    }
}
