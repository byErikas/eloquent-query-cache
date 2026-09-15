<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Tests\Models\Item;

$factory->define(Item::class, function (): array {
	return [
		"keyword" => "item-" . Str::uuid(),
	];
});
