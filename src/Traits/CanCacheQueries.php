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
	 * Should the default cache observer be attached.
	 */
	protected static bool $useDefaultCacheObserver = true;

	public static function bootCanCacheQueries(): void
	{
		static::whenBooted(function (): void {
			if (isset(static::$useDefaultCacheObserver) && static::$useDefaultCacheObserver) {
				static::observe(QueryCacheObserver::class);
			}

			if (method_exists(static::class, "getQueryCacheObservers")) {
				static::observe(static::getQueryCacheObservers());
			}
		});
	}

	public function getCacheTags(): array
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
