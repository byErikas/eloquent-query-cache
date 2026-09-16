<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Contracts\Config\Repository;
use Tests\Models\Item;

abstract class TestCase extends BaseTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->clearCache();
		$this->clearDatabase();

		$this->loadLaravelMigrations(["--database" => "sqlite"]);
		$this->loadMigrationsFrom(__DIR__ . "/Database/Migrations");

		$this->artisan("migrate", ["--database" => "sqlite"]);
	}

	protected function defineEnvironment($app): void
	{
		tap($app["config"], function (Repository $config): void {
			$config->set("app.key", "base64:6TshflJIHuaK4qQBMf3I5fnALXdH3n7IhLwh74mTKuw=");

			$config->set("cache.default", "redis");
			$config->set("cache.stores.redis", [
				"driver"   => "redis",
				"connection" => "default",
				"lock_connection" => "default",
			]);

			$config->set("database.default", "sqlite");
			$config->set("database.connections.sqlite", [
				"driver" => "sqlite",
				"database" => __DIR__ . "/Database/db.sqlite",
				"prefix" => "",
			]);

			$config->set("auth.providers.items.model", Item::class);
		});
	}

	public function clearCache(): void
	{
		$this->artisan("cache:clear");
	}

	public function clearDatabase(): void
	{
		file_put_contents(__DIR__ . "/Database/db.sqlite", null);
	}

	public function getSQLHash(string $sql): string
	{
		$database = __DIR__ . "/Database/db.sqlite";

		return "eqc:" . hash("xxh128", "{$database}:{$sql}");
	}
}
