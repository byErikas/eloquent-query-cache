<?php

declare(strict_types=1);

namespace ByErikas\EloquentQueryCache\Builder;

use DateTimeInterface;
use Illuminate\Cache\Repository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class QueryCacheBuilder extends Builder
{
	/**
	 * Cache prefix.
	 */
	protected string $cachePrefix = "eqc";

	/**
	 * Cache duration.
	 */
	protected int|DateTimeInterface|null $cacheFor = null;

	/**
	 * Additional cache tags used.
	 */
	protected ?array $cacheTags = null;

	/**
	 * Base model cache tags used.
	 */
	protected ?array $cacheBaseTags = null;

	/**
	 * Cache driver used.
	 */
	protected ?string $cacheDriver = null;

	public function get($columns = ["*"]): Collection
	{
		if ($this->cacheFor !== null) {
			return $this->getFromCache(Arr::wrap($columns));
		}

		return parent::get($columns);
	}

	public function useWritePdo(): parent
	{
		return parent::useWritePdo();
	}

	public function cacheTags(?array $tags = null): self
	{
		$this->cacheTags = $tags;

		return $this;
	}

	public function cacheBaseTags(?array $tags = null): self
	{
		$this->cacheBaseTags = $tags;

		return $this;
	}

	public function cacheFor(int|DateTimeInterface|null $cacheFor = null): self
	{
		$this->cacheFor = $cacheFor;

		return $this;
	}

	public function cacheForever(): self
	{
		$this->cacheFor = -1;

		return $this;
	}

	public function cacheDriver(?string $driver = null): self
	{
		$this->cacheDriver = $driver;

		return $this;
	}

	public function flushCache(array $tags = []): bool
	{
		$cache = $this->getCache($tags);

		return $cache->flush();
	}

	protected function getFromCache(array $columns = ["*"]): Collection
	{
		$key = $this->getCacheKey();
		$cache = $this->getCache();

		if ($this->cacheFor instanceof DateTimeInterface || $this->cacheFor > 0) {
			return $cache->remember($key, $this->cacheFor, function () use ($columns): Collection {
				return parent::get($columns);
			});
		}

		return $cache->rememberForever($key, function () use ($columns): Collection {
			return parent::get($columns);
		});
	}

	protected function getCacheTags(?array $tags = null): array
	{
		$base = $this->cacheBaseTags;

		if ($tags !== null) {
			$base = array_unique(array_merge($base, $tags));
		}

		if ($this->cacheTags !== null) {
			return array_unique(array_merge($base, $this->cacheTags));
		}

		return $base;
	}

	protected function getCacheKey(): string
	{
		$database = $this->connection->getDatabaseName();
		$sql = $this->toRawSql();

		return "{$this->cachePrefix}:" . hash("xxh128", "{$database}:{$sql}");
	}

	protected function getCache(?array $tags = null): Repository
	{
		$cache = app("cache")->driver($this->cacheDriver);

		if ($cache->supportsTags()) {
			$tags = $this->getCacheTags($tags);

			return $cache->tags($tags);
		}

		return $cache;
	}
}
