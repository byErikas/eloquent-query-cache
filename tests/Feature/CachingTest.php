<?php

use ByErikas\EloquentQueryCache\Builder\QueryCacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tests\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Tests\Models\MethodsItem;
use Tests\Models\PropertiesItem;
use Tests\Models\ObserversItem;

it("can cache using model properties defaults", function (): void {
	PropertiesItem::factory()->create();

	$items = PropertiesItem::get();

	Model::withoutEvents(function (): void {
		PropertiesItem::factory()->create();
	});

	expect(PropertiesItem::get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(PropertiesItem::get()->first()->keyword);
	expect(PropertiesItem::cacheFor(null)->get()->count())->toBe(2);
});

it("can cache using model methods defaults", function (): void {
	MethodsItem::factory()->create();

	$items = MethodsItem::get();

	Model::withoutEvents(function (): void {
		MethodsItem::factory()->create();
	});

	expect(MethodsItem::get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(MethodsItem::get()->first()->keyword);
	expect(MethodsItem::cacheFor(null)->get()->count())->toBe(2);
});

it("can flush cache using model defaults", function (): void {
	MethodsItem::factory()->create();

	/** Cached forever, with extra tags */
	$items = MethodsItem::get();

	Model::withoutEvents(function (): void {
		MethodsItem::factory()->create();
	});

	expect(MethodsItem::get()->count())->toEqual($items->count());

	MethodsItem::flushCache();
	expect(MethodsItem::get()->count())->toBe(2);
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

it("can handle consecutive observer events", function (): void {
	$item = ObserversItem::factory()->create();

	$items = ObserversItem::cacheFor(now()->addMinute())->get();

	expect(ObserversItem::cacheFor(now()->addMinute())->get()->count())->toBe($items->count());

	$item->delete();

	expect(ObserversItem::cacheFor(now()->addMinute())->get()->count())->toBe(0);

	$item->restore();

	expect(ObserversItem::cacheFor(now()->addMinute())->get()->count())->toBe(1);

	$item->forceDelete();

	expect(ObserversItem::cacheFor(now()->addMinute())->get()->count())->toBe(0);
});

it("can handle cache stores without tags", function (): void {
	Item::factory()->create();

	$items = Item::cacheTags(["items-2"])->cacheFor(now()->addMinute())->cacheDriver("file")->get();

	expect(Item::cacheDriver("file")->cacheFor(now()->addMinute())->get()->count())->toBe($items->count());
});

it("can cache plucks", function (): void {
	Item::factory()->create();

	$keywords = Item::cacheFor(now()->addMinute())->pluck("keyword");

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	$keywordsCached = Item::cacheFor(now()->addMinute())->pluck("keyword");
	expect($keywords->all())->toEqual($keywordsCached->all());

	$keywordsUncached = Item::cacheFor(null)->pluck("keyword");
	expect(count($keywordsUncached->all()))->toBe(2);
});

it("can cache exists temporarily", function (): void {
	$item = Item::factory()->create();

	$exists = Item::cacheFor(now()->addMinute())->exists();

	expect($exists)->toBe(true);

	Model::withoutEvents(function () use ($item): void {
		$item->delete();
	});

	$exists = Item::cacheFor(now()->addMinute())->exists();
	expect($exists)->toBe(true);

	$exists = Item::cacheFor(null)->exists();
	expect($exists)->toBe(false);
});

it("can cache exists forever", function (): void {
	$item = Item::factory()->create();

	$exists = Item::cacheForever()->exists();

	expect($exists)->toBe(true);

	Model::withoutEvents(function () use ($item): void {
		$item->delete();
	});

	$exists = Item::cacheForever()->exists();
	expect($exists)->toBe(true);

	$exists = Item::cacheFor(null)->exists();
	expect($exists)->toBe(false);
});
