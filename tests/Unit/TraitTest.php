<?php

use Tests\Models\Item;

it("can cache gets", function (): void {
	$this->clearCache();

	Item::factory()->create();

	$items = Item::cacheFor(now()->addMinutes(5))->get();

	Item::factory()->create();

	expect(Item::cacheFor(now()->addMinutes(5))->get()->count())->toEqual($items->count());
	expect($items->first()->keyword)->toEqual(Item::cacheFor(now()->addMinutes(5))->get()->first()->keyword);
	expect(Item::get()->count())->toBe(2);
});
