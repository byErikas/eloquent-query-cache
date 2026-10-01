<?php

declare(strict_types=1);

namespace Tests\Models;

use ByErikas\EloquentQueryCache\Traits\CanCacheQueries;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\Database\Factories\ItemFactory;

#[UseFactory(ItemFactory::class)]
class MethodsItem extends Model
{
	use CanCacheQueries, SoftDeletes, HasFactory;

	protected $fillable = [
		"keyword",
	];

	protected function getCacheFor(): int
	{
		return -1;
	}

	protected function getCacheDriver(): ?string
	{
		return null;
	}

	protected function getCacheTags(): array
	{
		return [
			"extra-tag"
		];
	}
}
