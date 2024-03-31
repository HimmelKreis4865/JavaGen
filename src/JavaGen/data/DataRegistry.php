<?php

declare(strict_types=1);

namespace JavaGen\data;

use const DIRECTORY_SEPARATOR;

final class DataRegistry {

	private const BASE = __DIR__ . DIRECTORY_SEPARATOR;

	public const MAPPINGS_FILE = self::BASE . "mappings.json";

	public const LOOT_TABLES = self::BASE . "lootTable" . DIRECTORY_SEPARATOR;
}