<?php

declare(strict_types=1);

namespace JavaGen\loot;

use JavaGen\data\DataRegistry;
use JavaGen\helper\number\Number;
use JavaGen\loot\item\EnchantItemFunction;
use JavaGen\loot\item\LootItem;
use JavaGen\loot\item\LootItemFunction;
use JavaGen\loot\item\SetCountFunction;
use JavaGen\loot\item\SetDamageFunction;
use JavaGen\loot\item\SetDataFunction;
use JavaGen\loot\item\SpecificEnchantFunction;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\SingletonTrait;
use RuntimeException;
use function array_diff;
use function basename;
use function file_get_contents;
use function is_array;
use function is_dir;
use function json_decode;
use function scandir;
use function str_ends_with;
use function str_replace;
use function strtolower;
use const DIRECTORY_SEPARATOR;

final class LootTableRegistry {
	use SingletonTrait;

	/**
	 * @var LootTable[] $lootTables
	 * @phpstan-var array<string, LootTable> $lootTables
	 */
	private array $lootTables = [];

	public function __construct() {
		$this->loadTables();
	}

	public function getTableByName(string $name): ?LootTable {
		return $this->lootTables[$this->parseName($name)] ?? null;
	}

	public function registerLootTable(string $name, LootTable $lootTable): void {
		$this->lootTables[$this->parseName($name)] = $lootTable;
	}

	private function parseName(string $name): string {
		return str_replace("_", "", strtolower($name));
	}

	private function loadTables(string $folder = DataRegistry::LOOT_TABLES, string $prefix = "chests/"): void {
		foreach (array_diff(scandir($folder), [".", ".."]) as $file) {
			if (is_dir($folder . $file)) {
				$this->loadTables($folder . $file . DIRECTORY_SEPARATOR, $prefix . $file . "/");
				continue;
			}
			if (!str_ends_with($file, ".json")) continue;
			$json = json_decode(file_get_contents($folder . $file), true);
			if (!is_array($json)) {
				throw new RuntimeException("Failed to decode loot table data for " . $file);
			}
			$this->loadTable($prefix . basename($file, ".json"), $json);
		}
	}

	/**
	 * @phpstan-param array<mixed> $jsonData
  	 */
	private function loadTable(string $name, array $jsonData): void {
		$pools = [];
		foreach ($jsonData["pools"] as $poolData) {
			$pool = LootPool::fromSize(Number::fromJson($poolData["rolls"]));

			foreach ($poolData["entries"] as $entry) {
				if ($entry["type"] !== "item" and $entry["type"] !== "empty") continue;

				$item = ($entry["type"] === "empty" ? VanillaItems::AIR() : StringToItemParser::getInstance()->parse($entry["name"]));
				if ($item === null) continue;

				$lootItem = LootItem::fromItem($item);
				if (isset($entry["weight"])) $lootItem->setWeight($entry["weight"]);

				foreach ($entry["functions"] ?? [] as $function) {
					$parsedFunction = self::findFunction($function["function"], $function);
					if ($parsedFunction === null) {
						continue;
					}
					$lootItem->apply($parsedFunction);
				}
				$pool->addItem($lootItem);
			}
			$pools[] = $pool;
		}

		$this->registerLootTable($name, new LootTable(...$pools));
	}

	private static function findFunction(string $functionName, array $json): ?LootItemFunction {
		return match ($functionName) {
			"minecraft:set_count", "set_count" => SetCountFunction::fromJson($json),
			"minecraft:set_damage", "set_damage" => SetDamageFunction::fromJson($json),
			"minecraft:enchant_randomly", "enchant_randomly" => EnchantItemFunction::fromJson($json),
			"minecraft:enchant_with_levels", "enchant_with_levels" => EnchantItemFunction::fromJson($json),
			"minecraft:set_data", "set_data" => SetDataFunction::fromJson($json),
			"minecraft:specific_enchants", "specific_enchants" => SpecificEnchantFunction::fromJson($json),
			default => null
		};
	}
}