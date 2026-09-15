<?php

declare(strict_types=1);

namespace ByErikas\EloquentQueryCache\Traits;

use ByErikas\EloquentQueryCache\Builder\QueryCacheBuilder;
use ByErikas\EloquentQueryCache\Observers\QueryCacheObserver;
use DateTimeInterface;
use Illuminate\Database\Query\Builder;

trait CanCacheQueries
{
	/**
	 * Cache duration
	 */
	protected int|DateTimeInterface|null $cacheFor = null;

	/**
	 * Cache tags used for element tagging
	 */
	protected ?array $cacheTags = null;

	/**
	 * Invalidate the cache automatically
	 * upon update in the database.
	 */
	protected static bool $flushCacheOnUpdate = true;

	public static function bootCanCacheQueries(): void
	{
		if (isset(static::$flushCacheOnUpdate) && static::$flushCacheOnUpdate) {
			static::whenBooted(function (): void {
				static::observe(QueryCacheObserver::class);

				if (method_exists(static::class, "getQueryCacheObservers")) {
					static::observe(static::getQueryCacheObservers());
				}
			});
		}
	}

	protected function getCacheTags(): array
	{
		$base = [$this->table];

		if ($this->cacheTags !== null) {
			return array_merge($base, $this->cacheTags);
		}

		return $base;
	}

	protected function newBaseQueryBuilder(): Builder
	{
		$connection = $this->getConnection();
		$grammar = $connection->getQueryGrammar();
		$postProcessor = $connection->getPostProcessor();

		if ($this->cacheFor === null) {
			return new Builder($connection, $grammar, $postProcessor);
		}

		return new QueryCacheBuilder($connection, $grammar, $postProcessor)
			->cacheFor($this->cacheFor)
			->cacheTags($this->getCacheTags());
	}
}
