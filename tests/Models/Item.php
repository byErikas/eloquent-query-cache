<?php

declare(strict_types=1);

namespace Tests\Models;

use ByErikas\EloquentQueryCache\Traits\CanCacheQueries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
	use CanCacheQueries, SoftDeletes, HasFactory;

	protected $fillable = [
		"keyword",
	];
}
