<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Tests\Models\Item;

it("can cache gets", function (): void {
	Item::factory()->create();

	$items = Item::cacheFor(now()->addMinutes(5))->get();

	Model::withoutEvents(function (): void {
		Item::factory()->create();
	});

	expect(Item::cacheFor(now()->addMinutes(5))->get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(Item::cacheFor(now()->addMinutes(5))->get()->first()->keyword);
	expect(Item::get()->count())->toBe(2);
});


it("can flush cache", function (): void {
	Item::factory()->create();

	Item::cacheFor(now()->addMinutes(5))->get();
	Item::flushCache();

	$cacheKey = $this->getSQLHash('select * from "items" where "items"."deleted_at" is null');

	expect(Cache::tags(["items"])->has($cacheKey))->toBeFalse();

	Item::cacheFor(now()->addMinutes(5))->get();

	expect(Cache::tags(["items"])->has($cacheKey))->toBeTrue();
});
