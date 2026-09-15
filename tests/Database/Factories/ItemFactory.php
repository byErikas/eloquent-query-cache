<?php

declare(strict_types=1);

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemFactory extends Factory
{
	public function definition(): array
	{
		return [
			"keyword" => "item-" . Str::uuid(),
		];
	}
}
