<?php

use Tests\Models\Item;

it("can cache gets", function () {
	$this->clearCache();

	factory(Item::class)->create();

	$items = Item::cacheFor(now()->addMinutes(5))->get();

	factory(Item::class)->create();

	expect(Item::cacheFor(now()->addMinutes(5))->get())->toEqual($items);
});
