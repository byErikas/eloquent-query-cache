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
class PropertiesItem extends Model
{
	use CanCacheQueries, SoftDeletes, HasFactory;

	protected $fillable = [
		"keyword",
	];

	protected ?string $cacheDriver = null;

	protected int $cacheFor = -1;

	protected array $cacheTags = ["extra-tag"];

	protected array $cacheBaseTags = ["extra-tag-base"];
}
