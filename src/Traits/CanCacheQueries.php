<?php

declare(strict_types=1);

namespace ByErikas\EloquentQueryCache\Traits;

use ByErikas\EloquentQueryCache\Builder\QueryCacheBuilder;
use ByErikas\EloquentQueryCache\Observers\QueryCacheObserver;
use Illuminate\Database\Query\Builder;

trait CanCacheQueries
{
	public static function bootCanCacheQueries(): void
	{
		static::whenBooted(function (): void {
			if (!isset(static::$ignoreDefaultCacheObserver) || (isset(static::$ignoreDefaultCacheObserver) && static::$ignoreDefaultCacheObserver == false)) {
				static::observe(QueryCacheObserver::class);
			}

			if (method_exists(static::class, "getQueryCacheObservers")) {
				static::observe(static::getQueryCacheObservers());
			}
		});
	}

	/**
	 * Returns cache tags used for all cached queries of model.
	 * 
	 * Always includes the model's table, merged with the `$cacheTags` property.
	 */
	public function getCacheTags(): array
	{
		$base = [$this->getTable()];

		if (property_exists($this, "cacheTags") && $this->cacheTags !== null) {
			return array_unique(array_merge($base, $this->cacheTags));
		}

		return $base;
	}

	protected function newBaseQueryBuilder(): Builder
	{
		$connection = $this->getConnection();
		$grammar = $connection->getQueryGrammar();
		$postProcessor = $connection->getPostProcessor();

		$builder =  new QueryCacheBuilder($connection, $grammar, $postProcessor)
			->cacheBaseTags($this->getCacheTags());

		if (property_exists($this, "cacheFor")) {
			$builder->cacheFor($this->cacheFor);
		}

		return $builder;
	}
}
