<?php

declare(strict_types=1);

namespace Tests\Models;

use ByErikas\EloquentQueryCache\Traits\CanCacheQueries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
	use CanCacheQueries, SoftDeletes;

	protected $fillable = [
		"keyword",
	];
}
