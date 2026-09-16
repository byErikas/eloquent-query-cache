<?php

use ByErikas\EloquentQueryCache\Builder\QueryCacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tests\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Tests\Models\DefaultItem;

it("can cache using model defaults", function (): void {
	DefaultItem::factory()->create();

	/** Cached forever, with extra tags */
	$items = DefaultItem::get();

	Model::withoutEvents(function (): void {
		DefaultItem::factory()->create();
	});

	expect(DefaultItem::get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(DefaultItem::get()->first()->keyword);
	expect(DefaultItem::cacheFor(null)->get()->count())->toBe(2);
});

it("can flush cache using model defaults", function (): void {
	DefaultItem::factory()->create();

	/** Cached forever, with extra tags */
	$items = DefaultItem::get();

	Model::withoutEvents(function (): void {
		DefaultItem::factory()->create();
	});

	expect(DefaultItem::get()->count())->toEqual($items->count());

	DefaultItem::flushCache();
	expect(DefaultItem::get()->count())->toBe(2);
});

it("can cache for timeframe", function (): void {
	Item::factory()->create();

	$items = Item::cacheFor(now()->addMinute())->get();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheFor(now()->addMinute())->get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(Item::cacheFor(now()->addMinute())->get()->first()->keyword);
	expect(Item::cacheFor(null)->get()->count())->toBe(2);
});

it("can cache forever", function (): void {
	Item::factory()->create();

	$items = Item::cacheForever()->get();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheForever()->get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(Item::cacheForever()->get()->first()->keyword);
	expect(Item::cacheFor(null)->get()->count())->toBe(2);
});

it("can re-cache expired results", function (): void {
	Item::factory()->create();

	$items = Item::cacheFor(now()->addMinute())->get();

	expect(Item::cacheFor(now()->addMinute())->get()->count())->toEqual($items->count());

	$this->clearCache();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheFor(now()->addMinute())->get()->count())->toBe(2);
});

it("can flush cache", function (): void {
	Item::factory()->create();

	Item::cacheFor(now()->addMinute())->get();
	Item::flushCache();

	$cacheKey = $this->getSQLHash(Item::toRawSql());

	expect(Cache::tags(["items"])->has($cacheKey))->toBeFalse();

	Item::cacheFor(now()->addMinute())->get();

	expect(Cache::tags(["items"])->has($cacheKey))->toBeTrue();
});

it("can cache by multiple tags", function (): void {
	Item::factory()->create();

	Item::cacheTags([
		"items-2"
	])->cacheFor(now()->addMinute())
		->get();

	$cacheKey = $this->getSQLHash(Item::toRawSql());

	expect(Cache::tags(["items", "items-2"])->has($cacheKey))->toBeTrue();
});

it("can bypass using query cache when using writePdo", function (): void {
	$query = Item::cacheForever()->useWritePdo();
	expect(get_class($query))->toBe(Builder::class);

	$query = Item::cacheForever()->getQuery();
	expect(get_class($query))->toBe(QueryCacheBuilder::class);
});


it("can set cache driver, and retrieve from cache driver", function (): void {
	Item::factory()->create();

	$items = Item::cacheFor(now()->addMinute())->cacheDriver("array")->get();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheFor(now()->addMinute())->cacheDriver("array")->get()->count())->toBe($items->count());

	expect(Item::cacheFor(now()->addMinute())->get()->count())->toBe(2);
});
