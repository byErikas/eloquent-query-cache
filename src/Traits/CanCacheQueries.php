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
			if (!isset(static::$attachDefaultCacheObserver) || (isset(static::$attachDefaultCacheObserver) && static::$attachDefaultCacheObserver)) {
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
	 * Always includes the model's table, merged with the `$cacheBaseTags` property.
	 */
	public function getCacheBaseTags(): array
	{
		$base = [$this->getTable()];

		$extra = [];

		if (property_exists($this, "cacheBaseTags") && $this->cacheBaseTags !== null) {
			$extra = $this->cacheBaseTags;
		}

		return array_unique(array_merge($base, $extra));
	}

	protected function newBaseQueryBuilder(): Builder
	{
		$connection = $this->getConnection();
		$grammar = $connection->getQueryGrammar();
		$postProcessor = $connection->getPostProcessor();

		$builder = new QueryCacheBuilder($connection, $grammar, $postProcessor)
			->cacheBaseTags($this->getCacheBaseTags());

		if (property_exists($this, "cacheFor")) {
			$builder->cacheFor($this->cacheFor);
		}

		if (property_exists($this, "cacheTags")) {
			$builder->cacheTags($this->cacheTags);
		}

		if (property_exists($this, "cacheDriver")) {
			$builder->cacheDriver($this->cacheDriver);
		}

		if (method_exists($this, "getCacheFor")) {
			$builder->cacheFor($this->getCacheFor($builder));
		}

		if (method_exists($this, "getCacheTags")) {
			$builder->cacheTags($this->getCacheTags($builder));
		}

		if (method_exists($this, "getCacheDriver")) {
			$builder->cacheDriver($this->getCacheDriver($builder));
		}

		return $builder;
	}
}
