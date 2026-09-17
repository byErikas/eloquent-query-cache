<?php

declare(strict_types=1);

namespace Tests\Models;

use ByErikas\EloquentQueryCache\Traits\CanCacheQueries;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\Database\Factories\ItemFactory;
use Tests\Observers\TestObserver;

#[UseFactory(ItemFactory::class)]
class DefaultItem extends Model
{
	use CanCacheQueries, SoftDeletes, HasFactory;

	protected $fillable = [
		"keyword",
	];

	protected static $attachDefaultCacheObserver = true;

	protected int $cacheFor = -1;

	protected array $cacheTags = ["extra-tag"];

	public static function getQueryCacheObservers(): array
	{
		return [
			TestObserver::class
		];
	}
}
