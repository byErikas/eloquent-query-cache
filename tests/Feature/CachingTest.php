<?php

use ByErikas\EloquentQueryCache\Builder\QueryCacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tests\Models\Item;
use Illuminate\Database\Eloquent\Builder;

it("can cache for timeframe", function (): void {
	Item::factory()->create();

	$items = Item::cacheFor(now()->addMinutes(5))->get();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheFor(now()->addMinutes(5))->get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(Item::cacheFor(now()->addMinutes(5))->get()->first()->keyword);
	expect(Item::get()->count())->toBe(2);
});

it("can cache forever", function (): void {
	Item::factory()->create();

	$items = Item::cacheForever()->get();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheForever()->get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(Item::cacheForever()->get()->first()->keyword);
	expect(Item::get()->count())->toBe(2);
});

it("can re-cache expired results", function (): void {
	Item::factory()->create();

	$items = Item::cacheFor(now()->addSeconds(1))->get();

	expect(Item::cacheFor(now()->addSeconds(1))->get()->count())->toEqual($items->count());

	$this->clearCache();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheFor(now()->addSeconds(1))->get()->count())->toBe(2);
});

it("can flush cache", function (): void {
	Item::factory()->create();

	Item::cacheFor(now()->addMinutes(5))->get();
	Item::flushCache();

	$cacheKey = $this->getSQLHash(Item::toRawSql());

	expect(Cache::tags(["items", "extra-tag"])->has($cacheKey))->toBeFalse();

	Item::cacheFor(now()->addMinutes(5))->get();

	expect(Cache::tags(["items", "extra-tag"])->has($cacheKey))->toBeTrue();
});

it("can cache by multiple tags", function (): void {
	Item::factory()->create();

	Item::cacheTags([
		"items-2"
	])->cacheFor(now()->addMinutes(5))
		->get();

	$cacheKey = $this->getSQLHash(Item::toRawSql());

	expect(Cache::tags(["items", "extra-tag", "items-2"])->has($cacheKey))->toBeTrue();
});

it("can bypass using query cache when using writePdo", function (): void {
	$query = Item::cacheForever()->useWritePdo();
	expect(get_class($query))->toBe(Builder::class);

	$query = Item::cacheForever()->getQuery();
	expect(get_class($query))->toBe(QueryCacheBuilder::class);
});
